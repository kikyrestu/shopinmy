<div class="fi-section-content p-6">
    <div class="prose dark:prose-invert max-w-none text-sm space-y-4">
        <h4 style="font-weight: bold; margin-bottom: 0.5rem;">📝 Variabel Pesan (Gunakan dalam kurung kurawal)</h4>
        <ul style="list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem;">
            <li><code>{name}</code> : Nama Pelanggan</li>
            <li><code>{phone}</code> : Nomor WA Pelanggan</li>
            <li><code>{otp_code}</code> : Kode OTP (Khusus Register/Login)</li>
            <li><code>{order_id}</code> : Nomor Pesanan (Invoice)</li>
            <li><code>{total}</code> : Total Harga Pesanan</li>
            <li><code>{receipt}</code> : Nomor Resi Pengiriman</li>
        </ul>
        
        <hr style="border-top: 1px solid #e5e7eb; margin: 1rem 0;">
        
        <h4 style="font-weight: bold; margin-bottom: 0.5rem;">⚙️ Daftar Kode Event Sistem (Penting!)</h4>
        <p style="margin-bottom: 0.5rem;">Isi kolom <b>Kode Event</b> HANYA dengan kode di bawah ini jika template ingin dikirim <b>Otomatis</b> oleh sistem:</p>
        <ul style="list-style-type: disc; margin-left: 1.5rem; margin-bottom: 1rem;">
            <li><code style="color: #0ea5e9;">otp_register</code> : Dikirim saat pendaftaran/login via OTP.</li>
            <li><code style="color: #0ea5e9;">order_success</code> : Dikirim otomatis setelah checkout pesanan berhasil.</li>
            <li><code style="color: #0ea5e9;">order_shipped</code> : Dikirim saat resi pengiriman telah diinput.</li>
        </ul>
        <p style="font-size: 0.75rem; font-style: italic; opacity: 0.8;">*Catatan: Kosongkan kolom Kode Event jika template ini HANYA digunakan untuk <b>Marketing WA Blast</b> (Kirim manual).*</p>
    </div>
</div>
