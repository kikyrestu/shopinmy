<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MyParcelService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $isSandbox = setting_bool('myparcel_sandbox') ?? (config('app.env') !== 'production');

        $this->baseUrl = $isSandbox
            ? 'https://demo.myparcelasia.com/apiv2/'
            : 'https://app.myparcelasia.com/apiv2/';

        $this->apiKey = setting('myparcel_api_key')
            ?? config('services.myparcel.api_key', '');

        $this->apiSecret = setting('myparcel_api_secret')
            ?? config('services.myparcel.api_secret', '');
    }

    // ─── Core HTTP ────────────────────────────────────────────

    /**
     * POST request to MyParcel API.
     * All endpoints are POST-only per docs.
     */
    protected function post(string $endpoint, array $data = []): array
    {
        $data['api_key'] = $this->apiKey;

        $http = Http::asForm()->timeout(30);

        // Hybrid mode: disable SSL verification only in local environment
        if (config('app.env') === 'local') {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($this->baseUrl . ltrim($endpoint, '/'), $data);

        if (!$response->successful()) {
            Log::error('MyParcel HTTP Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception("MyParcel API HTTP Error: {$response->status()}");
        }

        $json = $response->json();

        // Verify response signature if hash is present
        if (isset($json['hash'])) {
            if (!$this->verifyHash($json)) {
                Log::error('MyParcel API Hash Mismatch', ['endpoint' => $endpoint, 'response' => $json]);
                
                // If the API returned status false, the hash mismatch is usually a side effect.
                // It's better to show the actual API message (e.g. "Invalid API Key").
                if (isset($json['status']) && $json['status'] === false) {
                    $errorMessage = is_array($json['message'] ?? null) 
                        ? json_encode($json['message']) 
                        : ($json['message'] ?? 'Unknown Error');
                    throw new Exception("MyParcel: {$errorMessage}");
                }

                throw new Exception("MyParcel API Error: Hash verification failed (Invalid signature).");
            }
        }

        if (!($json['status'] ?? false)) {
            $message = is_array($json['message'] ?? null)
                ? json_encode($json['message'])
                : ($json['message'] ?? 'Unknown error');

            Log::warning('MyParcel API Error', [
                'endpoint' => $endpoint,
                'message' => $message,
            ]);

            throw new Exception("MyParcel: {$message}");
        }

        return $json['data'] ?? [];
    }

    /**
     * Verify response hash integrity.
     * hash = md5(json_encode([status, message, data, meta]) . api_secret)
     */
    public function verifyHash(array $response): bool
    {
        if (empty($response['hash']) || empty($this->apiSecret)) {
            return false;
        }

        $check = [
            'status' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data'],
            'meta' => $response['meta'] ?? null,
        ];

        return md5(json_encode($check) . $this->apiSecret) === $response['hash'];
    }

    // ─── User & Utilities ─────────────────────────────────────

    /**
     * GET user details (test connection).
     * Endpoint: /user
     */
    public function getUser(): array
    {
        return $this->post('user');
    }

    /**
     * Verify postcode → auto-populate city & state.
     * Endpoint: /get_postcode_details
     */
    public function getPostcodeDetails(string $postcode): array
    {
        return $this->post('get_postcode_details', [
            'postcode' => $postcode,
        ]);
    }

    /**
     * Get available parcel sizes (flyers_s, flyers_m, box, etc).
     * Endpoint: /get_parcel_sizes
     */
    public function getParcelSizes(): array
    {
        return $this->post('get_parcel_sizes');
    }

    /**
     * Get content type codes (general, health, gadget_general, etc).
     * Endpoint: /get_content_types
     */
    public function getContentTypes(): array
    {
        return $this->post('get_content_types');
    }

    /**
     * Get shipment status labels.
     * Endpoint: /get_shipment_statuses
     */
    public function getShipmentStatuses(): array
    {
        return $this->post('get_shipment_statuses');
    }

    // ─── Pricing ──────────────────────────────────────────────

    /**
     * Check shipping price for a single shipment.
     * Endpoint: /check_price
     *
     * Returns: array of provider options with prices.
     */
    public function checkPrice(string $senderPostcode, string $receiverPostcode, float $weight, ?string $receiverCountryCode = 'MY'): array
    {
        $params = [
            'sender_postcode' => $senderPostcode,
            'declared_weight' => $weight,
        ];

        if ($receiverCountryCode !== 'MY') {
            $params['receiver_country_code'] = $receiverCountryCode;
        } else {
            $params['receiver_postcode'] = $receiverPostcode;
        }

        return $this->post('check_price', $params);
    }

    /**
     * Same Day Delivery pricing (GrabX, Lalamove, etc).
     * Endpoint: /sdd_price
     */
    public function sddPrice(array $params): array
    {
        // Required: pickup_address, pickup_postcode, pickup_lat, pickup_lng,
        //           receiver_address, receiver_postcode, receiver_lat, receiver_lng,
        //           declared_weight
        return $this->post('sdd_price', $params);
    }

    /**
     * Bulk price check for multiple shipments.
     * Endpoint: /check_price_bulk
     */
    public function checkPriceBulk(array $shipments): array
    {
        return $this->post('check_price_bulk', [
            'shipments' => $shipments,
        ]);
    }

    // ─── Cart Flow (Shipment → Cart → Checkout) ───────────────

    /**
     * Create a single shipment (adds to MyParcel cart).
     * Endpoint: /create_shipment
     *
     * Returns: shipment data including shipment_key.
     */
    public function createShipment(array $shipmentData): array
    {
        return $this->post('create_shipment', $shipmentData);
    }

    /**
     * Get all shipments in cart (ready to checkout).
     * Endpoint: /get_cart_items
     */
    public function getCartItems(): array
    {
        return $this->post('get_cart_items');
    }

    /**
     * Checkout shipments → deducts balance, returns tracking_no & label_url.
     * Endpoint: /checkout
     *
     * @param array $shipmentKeys Array of shipment_key strings
     */
    public function checkout(array $shipmentKeys): array
    {
        return $this->post('checkout', [
            'shipment_keys' => $shipmentKeys,
        ]);
    }

    // ─── Bulk Flow (Create + Checkout in one call) ────────────

    /**
     * Create multiple AWBs in one call (auto-checkout).
     * Endpoint: /create_bulk_awb
     *
     * Returns: tracking_no array + awb_url (PDF download).
     */
    public function createBulkAwb(array $shipments): array
    {
        return $this->post('create_bulk_awb', [
            'shipments' => $shipments,
        ]);
    }

    // ─── Shipment Management ──────────────────────────────────

    /**
     * Get shipment details by keys.
     * Endpoint: /get_shipments
     */
    public function getShipments(array $shipmentKeys): array
    {
        return $this->post('get_shipments', [
            'shipment_keys' => $shipmentKeys,
        ]);
    }

    /**
     * Get paid shipment history (paginated).
     * Endpoint: /get_shipment_history
     */
    public function getShipmentHistory(int $page = 1): array
    {
        return $this->post('get_shipment_history', [
            'page' => $page,
        ]);
    }

    /**
     * Download consignment note PDF.
     * Endpoint: /get_consignment_note
     *
     * @param array|null $shipmentKeys
     * @param array|null $trackingNumbers  (takes priority if both provided)
     */
    public function getConsignmentNote(?array $shipmentKeys = null, ?array $trackingNumbers = null): array
    {
        $params = [];

        if ($trackingNumbers) {
            $params['tracking_no'] = $trackingNumbers;
        } elseif ($shipmentKeys) {
            $params['shipment_keys'] = $shipmentKeys;
        }

        return $this->post('get_consignment_note', $params);
    }

    /**
     * Trace shipment — get latest delivery status.
     * Endpoint: /trace
     */
    public function trace(string $trackingNo): array
    {
        return $this->post('trace', [
            'tracking_no' => $trackingNo,
        ]);
    }

    // ─── High-Level Helpers ───────────────────────────────────

    /**
     * Build shipment data array from an Order model.
     * Used by OrdersTable "Generate AWB" action.
     */
    public function buildShipmentFromOrder(\App\Models\Order $order): array
    {
        $order->load(['user', 'address', 'items.product']);

        $address = $order->address;
        $user = $order->user;

        // Calculate total weight from items
        $totalWeight = $order->items->sum(function ($item) {
            return ($item->product?->weight ?? 0.5) * $item->qty;
        });
        $totalWeight = max($totalWeight, 0.1); // min 100g

        // Build line_item data for invoice on AWB
        $lineItems = $order->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'name' => $item->product?->name ?? 'Product',
            'sku' => $item->product?->sku ?? '-',
            'weight' => (string) ($item->product?->weight ?? 0.5),
            'sub_weight' => ($item->product?->weight ?? 0.5) * $item->qty,
            'currency' => 'MYR',
            'quantity' => $item->qty,
            'price' => (string) $item->price,
            'sub_total' => (string) ($item->price * $item->qty),
        ])->toArray();

        return [
            'integration_order_id' => (string) $order->id,
            'integration_order_data' => [
                'connote_show_invoice' => 'yes',
                'total_weight' => $totalWeight,
                'line_item' => $lineItems,
            ],
            'send_method' => setting('myparcel_send_method') ?? 'pickup',
            'send_date' => now()->addDay()->isWeekend() ? now()->next('Monday')->format('Y-m-d') : now()->addDay()->format('Y-m-d'),
            'type' => 'parcel',
            'declared_weight' => round($totalWeight, 3),
            'size' => setting('myparcel_default_size') ?? 'flyers_l',
            'provider_code' => $this->normalizeProviderCode($order->courier ?? setting('myparcel_default_provider') ?? 'poslaju'),
            'content_type' => 'general',
            'content_description' => 'E-Commerce Purchase #' . $order->id,
            'content_value' => (string) $order->total,

            // Sender (from settings)
            'sender_name' => setting('site_name') ?? config('app.name'),
            'sender_phone' => setting('site_phone') ?? '0123456789',
            'sender_email' => setting('site_email') ?? '',
            'sender_address_line_1' => setting('site_address') ?? '',
            'sender_postcode' => setting('store_postcode') ?? '',

            // Receiver
            'receiver_name' => $user?->name ?? $order->guest_name ?: 'Customer',
            'receiver_phone' => $user?->phone ?? $order->guest_phone ?: '0123456789',
            'receiver_email' => $user?->email ?? $order->guest_email ?: 'no-reply@commbuildy.com',
            'receiver_address_line_1' => $address?->address ?? $order->guest_address ?: 'No Address',
            'receiver_address_line_2' => $address?->city ?? $order->guest_city ?: 'City',
            'receiver_postcode' => $address?->postcode ?? $order->guest_postcode ?: '00000',
            'receiver_state' => $address?->state ?? $order->guest_state ?: 'State',
            'receiver_country_code' => 'MY',
        ];
    }

    /**
     * Map old courier names to valid MyParcel provider codes.
     */
    private function normalizeProviderCode(string $courier): string
    {
        $code = strtolower(trim($courier));
        
        // If it's already a valid code, return it
        $validCodes = ['jnt', 'lex', 'spx', 'flash', 'poslaju', 'ninjavan', 'dhle', 'citylink', 'fmx', 'bestexpress', 'kexexpress'];
        if (in_array($code, $validCodes)) {
            return $code;
        }

        // Fallback mappings for old DB records
        $map = [
            'j&t' => 'jnt',
            'j&t express' => 'jnt',
            'poslaju' => 'poslaju',
            'pos laju' => 'poslaju',
            'ninja van' => 'ninjavan',
            'city-link' => 'citylink',
            'citylink express' => 'citylink',
            'shopee express' => 'spx',
            'dhl' => 'dhle',
            'dhl ecommerce' => 'dhle',
        ];

        return $map[$code] ?? 'poslaju';
    }

    /**
     * Full flow: create shipment → checkout → get tracking.
     * Returns tracking_no string, null if pending, or throws Exception.
     */
    public function generateAwbForOrder(\App\Models\Order $order): ?string
    {
        $shipmentData = $this->buildShipmentFromOrder($order);

        // Step 1: Create shipment
        $shipment = $this->createShipment($shipmentData);
        $shipmentKey = $shipment['shipment_key'] ?? null;

        if (!$shipmentKey) {
            throw new Exception('MyParcel: No shipment_key returned');
        }

        // Step 2: Checkout (pay & get connote)
        $checkout = $this->checkout([$shipmentKey]);
        $shipments = $checkout['shipments'] ?? [];
        $firstShipment = reset($shipments);

        if (empty($firstShipment)) {
            $responseJson = json_encode($checkout);
            throw new Exception('MyParcel: Checkout failed - no shipment data returned. ' . $responseJson);
        }

        if (isset($checkout['status']) && $checkout['status'] !== 'paid') {
            $responseJson = json_encode($checkout);
            throw new Exception('MyParcel: Checkout failed - payment not successful. ' . $responseJson);
        }

        // Some couriers like SPX might return tracking_no as null initially (generated asynchronously)
        $trackingNo = $firstShipment['tracking_no'] ?? null;
        $labelUrl = $firstShipment['label_url'] ?? $firstShipment['consignment_note'] ?? null;

        // Step 3: Update order
        $order->update([
            'tracking_no' => $trackingNo,
            'awb_label_url' => $labelUrl,
            'courier' => $shipment['provider_code'] ?? 'poslaju',
            'status' => 'processing',
        ]);

        Log::info('AWB Generated', [
            'order_id' => $order->id,
            'tracking_no' => $trackingNo,
            'label_url' => $labelUrl,
        ]);

        return $trackingNo;
    }

    /**
     * Sync tracking number for a pending order from MyParcel history.
     */
    public function syncTrackingNumber(\App\Models\Order $order): ?string
    {
        for ($page = 1; $page <= 5; $page++) {
            $history = $this->getShipmentHistory($page);
            if (empty($history['data'])) break;

            foreach ($history['data'] as $shipment) {
                if (($shipment['integration_order_id'] ?? null) == $order->id) {
                    if (!empty($shipment['tracking_no'])) {
                        $order->update([
                            'tracking_no' => $shipment['tracking_no'],
                            'awb_label_url' => $shipment['label_url'] ?? $shipment['consignment_note'] ?? null,
                            'status' => 'processing',
                        ]);
                        return $shipment['tracking_no'];
                    }
                }
            }
        }
        return null;
    }
}
