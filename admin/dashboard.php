<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA-Kasir</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Geist','Inter','sans-serif'] }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">
<script> const KASIR_NAME = "<?php echo htmlspecialchars($userName ?? 'Kasir'); ?>"; </script>
  <div id="receiptPrint" class="hidden font-mono text-xs"></div>

  <!-- Toast Container -->
  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- ====== ADD PRODUCT MODAL ====== -->
  <div id="addProductModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeAddProductModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 px-6 py-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <iconify-icon icon="solar:add-square-bold" width="22" class="text-white"></iconify-icon>
            </div>
            <div>
              <h2 class="text-lg font-bold text-white">Tambah Produk Baru</h2>
              <p class="text-xs text-white/70">Isi data produk sembako</p>
            </div>
          </div>
          <button onclick="closeAddProductModal()" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all">
            <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
          </button>
        </div>
      </div>
      <!-- Form -->
      <form id="addProductForm" onsubmit="handleAddProduct(event)" class="p-6 space-y-4">
        <!-- Nama Produk -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Nama Produk <span class="text-red-400">*</span></label>
          <div class="relative">
            <iconify-icon icon="solar:box-minimalistic-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="text" name="nama_produk" required placeholder="Contoh: Beras Premium" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
          </div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Deskripsi <span class="text-red-400">*</span></label>
          <textarea name="deskripsi" required rows="2" placeholder="Deskripsi singkat produk, akan tampil di toko pelanggan" class="w-full px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm resize-none"></textarea>
        </div>

        <!-- Harga & Stok -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Harga (Rp) <span class="text-red-400">*</span></label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-neutral-400">Rp</span>
              <input type="number" name="harga" required min="1" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Stok <span class="text-red-400">*</span></label>
            <div class="relative">
              <iconify-icon icon="solar:layers-minimalistic-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
              <input type="number" name="stok" required min="0" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
            </div>
          </div>
        </div>

        <!-- Kategori & Satuan -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Kategori <span class="text-red-400">*</span></label>
            <div class="relative">
              <iconify-icon icon="solar:tag-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
              <select name="id_kategori" id="formKategori" required class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm appearance-none">
                <option value="">— Pilih —</option>
              </select>
              <iconify-icon icon="solar:alt-arrow-down-linear" width="16" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none"></iconify-icon>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Satuan</label>
            <div class="relative">
              <iconify-icon icon="solar:ruler-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
              <select name="satuan" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm appearance-none">
                <option value="pcs">Pcs</option>
                <option value="kg">Kg</option>
                <option value="liter">Liter</option>
                <option value="bks">Bungkus</option>
                <option value="btl">Botol</option>
                <option value="dos">Dus</option>
              </select>
              <iconify-icon icon="solar:alt-arrow-down-linear" width="16" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none"></iconify-icon>
            </div>
          </div>
        </div>

        <!-- Barcode (Belum ada alat barcode) -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Barcode <span class="text-neutral-400 font-normal">(opsional)</span></label>
          <div class="relative">
            <iconify-icon icon="solar:barcode-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="text" name="barcode" placeholder="Contoh: 8991234" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
          </div>
        </div>

        <!-- Upload Gambar -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Gambar Produk <span class="text-neutral-400 font-normal">(maks 2MB)</span></label>
          <div id="imgPreviewContainer" class="img-preview-container rounded-2xl bg-neutral-50 p-4 text-center cursor-pointer" onclick="document.getElementById('formGambar').click()">
            <input type="file" name="gambar" id="formGambar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" onchange="previewImage(this)">
            <div id="imgPlaceholder" class="flex flex-col items-center gap-2 py-4">
              <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center">
                <iconify-icon icon="solar:camera-add-bold" width="28" class="text-emerald-400"></iconify-icon>
              </div>
              <p class="text-sm font-medium text-neutral-500">Klik untuk upload gambar</p>
              <p class="text-xs text-neutral-400">JPG, PNG, WebP, GIF</p>
            </div>
            <img id="imgPreview" src="" alt="Preview" class="hidden max-h-40 mx-auto rounded-xl object-contain">
          </div>
        </div>

        <!-- Submit -->
        <button type="submit" id="addProductBtn" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-emerald-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all">
          <iconify-icon icon="solar:diskette-bold" width="18"></iconify-icon>
          Simpan Produk
        </button>
      </form>
    </div>
  </div>

  <!-- ====== DELETE CONFIRM MODAL ====== -->
  <div id="deleteConfirmModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-sm w-full shadow-2xl p-6 text-center">
      <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <iconify-icon icon="solar:trash-bin-minimalistic-bold" width="32" class="text-red-400"></iconify-icon>
      </div>
      <h3 class="text-lg font-bold text-neutral-900 mb-1">Hapus Produk?</h3>
      <p class="text-sm text-neutral-500 mb-5">Produk <strong id="deleteProductName" class="text-neutral-700"></strong> akan dihapus permanen.</p>
      <div class="flex gap-3">
        <button onclick="closeDeleteModal()" class="flex-1 py-2.5 rounded-xl border border-neutral-200 text-neutral-600 font-semibold text-sm hover:bg-neutral-50 transition-all">Batal</button>
        <button onclick="executeDeleteProduct()" id="confirmDeleteBtn" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white font-semibold text-sm hover:bg-red-600 transition-all">Ya, Hapus</button>
      </div>
    </div>
  </div>
    <!-- ====== RESTOCK MODAL ====== -->
  <div id="restockModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeRestockModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-sm w-full shadow-2xl p-6">
      <div class="flex items-center gap-3 mb-5">
        <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center">
          <iconify-icon icon="solar:box-minimalistic-bold" width="24" class="text-emerald-500"></iconify-icon>
        </div>
        <div>
          <h3 class="text-lg font-bold text-neutral-900">Tambah Stok</h3>
          <p class="text-sm text-neutral-400" id="restockProductName">-</p>
        </div>
      </div>
      <div class="mb-3">
        <div class="flex justify-between text-sm mb-1">
          <span class="text-neutral-500">Stok saat ini</span>
          <span class="font-bold text-neutral-700" id="restockCurrentStok">0</span>
        </div>
      </div>
      <div class="mb-5">
        <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Tambah Stok</label>
        <div class="relative">
          <iconify-icon icon="solar:add-square-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
          <input type="number" id="restockInput" min="1" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-base font-bold text-neutral-800 placeholder:text-neutral-300" oninput="updateRestockPreview()">
        </div>
        <div class="flex justify-between text-sm mt-2 px-1">
          <span class="text-neutral-500">Stok baru</span>
          <span class="font-bold text-emerald-600" id="restockNewStok">0</span>
        </div>
      </div>
      <div class="flex gap-3">
        <button onclick="closeRestockModal()" class="flex-1 py-2.5 rounded-xl border border-neutral-200 text-neutral-600 font-semibold text-sm hover:bg-neutral-50 transition-all">Batal</button>
        <button onclick="submitRestock()" id="restockSubmitBtn" class="flex-1 py-2.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 transition-all flex items-center justify-center gap-1.5">
          <iconify-icon icon="solar:check-circle-bold" width="16"></iconify-icon>
          Tambah
        </button>
      </div>
    </div>
  </div>
    <!-- ====== EDIT PRODUCT MODAL ====== -->
  <div id="editProductModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeEditProductModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-500 to-blue-700 px-6 py-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <iconify-icon icon="solar:pen-bold" width="22" class="text-white"></iconify-icon>
            </div>
            <div>
              <h2 class="text-lg font-bold text-white">Edit Produk</h2>
              <p class="text-xs text-white/70">Ubah data produk</p>
            </div>
          </div>
          <button onclick="closeEditProductModal()" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition-all">
            <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
          </button>
        </div>
      </div>
      <!-- Form -->
      <form id="editProductForm" onsubmit="handleEditProduct(event)" class="p-6 space-y-4">
        <input type="hidden" name="id_produk" id="editIdProduk">

        <!-- Nama -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Nama Produk <span class="text-red-400">*</span></label>
          <div class="relative">
            <iconify-icon icon="solar:box-minimalistic-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="text" name="nama_produk" id="editNama" required placeholder="Nama produk" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
          </div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Deskripsi <span class="text-neutral-400 font-normal">(opsional)</span></label>
          <textarea name="deskripsi" id="editDeskripsi" rows="2" placeholder="Deskripsi singkat produk, akan tampil di toko pelanggan" class="w-full px-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm resize-none"></textarea>
        </div>

        <!-- Harga & Stok -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Harga (Rp) <span class="text-red-400">*</span></label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-neutral-400">Rp</span>
              <input type="number" name="harga" id="editHarga" required min="1" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Stok <span class="text-red-400">*</span></label>
            <div class="relative">
              <iconify-icon icon="solar:layers-minimalistic-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
              <input type="number" name="stok" id="editStok" required min="0" placeholder="0" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
            </div>
          </div>
        </div>

        <!-- Kategori & Satuan -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Kategori <span class="text-red-400">*</span></label>
            <div class="relative">
              <iconify-icon icon="solar:tag-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
              <select name="id_kategori" id="editKategori" required class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm appearance-none">
                <option value="">— Pilih —</option>
              </select>
              <iconify-icon icon="solar:alt-arrow-down-linear" width="16" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none"></iconify-icon>
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Satuan</label>
            <div class="relative">
              <iconify-icon icon="solar:ruler-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
              <select name="satuan" id="editSatuan" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm appearance-none">
                <option value="pcs">Pcs</option>
                <option value="kg">Kg</option>
                <option value="liter">Liter</option>
                <option value="bks">Bungkus</option>
                <option value="btl">Botol</option>
                <option value="dos">Dus</option>
              </select>
              <iconify-icon icon="solar:alt-arrow-down-linear" width="16" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none"></iconify-icon>
            </div>
          </div>
        </div>

        <!-- Barcode -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Barcode <span class="text-neutral-400 font-normal">(opsional)</span></label>
          <div class="relative">
            <iconify-icon icon="solar:barcode-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="text" name="barcode" id="editBarcode" placeholder="Contoh: 8991234" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm">
          </div>
        </div>

        <!-- Gambar -->
        <div>
          <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Gambar Produk</label>
          <!-- Preview gambar lama -->
          <div id="editImgPreviewWrap" class="mb-2 hidden">
            <div class="relative inline-block">
              <img id="editImgPreview" src="" alt="Preview" class="h-28 rounded-xl border border-neutral-200">
              <button type="button" onclick="removeEditImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600 transition-all" title="Hapus gambar">
                <iconify-icon icon="solar:close-circle-bold" width="14"></iconify-icon>
              </button>
            </div>
          </div>
          <div class="relative">
            <iconify-icon icon="solar:camera-add-linear" width="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
            <input type="file" name="gambar" id="editGambar" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full pl-11 pr-4 py-3 bg-neutral-50 rounded-xl border border-neutral-200 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-600 file:cursor-pointer hover:file:bg-blue-100">
          </div>
          <p class="text-[11px] text-neutral-400 mt-1">Kosongkan jika tidak ingin mengubah gambar</p>
          <input type="hidden" name="hapus_gambar" id="editHapusGambar" value="0">
        </div>

        <!-- Submit -->
        <button type="submit" id="editProductBtn" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-blue-500 to-blue-700 text-white font-bold text-sm flex items-center justify-center gap-2 hover:shadow-lg hover:shadow-blue-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all">
          <iconify-icon icon="solar:pen-bold" width="18"></iconify-icon>
          Simpan Perubahan
        </button>
      </form>
    </div>
  </div>
  <!-- ====== SUCCESS MODAL ====== -->
  <div id="successModal" class="fixed inset-0 z-[9990] hidden items-center justify-center p-4">
    <div class="modal-overlay absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeSuccessModal()"></div>
    <div class="modal-content relative bg-white rounded-3xl max-w-md w-full shadow-2xl p-8 text-center">
      <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-5" style="animation: successPulse 0.6s ease">
        <iconify-icon icon="solar:check-circle-bold" width="48" class="text-emerald-500"></iconify-icon>
      </div>
      <h2 class="text-2xl font-bold text-neutral-900 mb-2">Pembayaran Berhasil!</h2>
      <p class="text-sm text-neutral-400 mb-1" id="modalNoTrx">No: TRX0000</p>
      <p class="text-neutral-500 mb-1" id="modalTotal">Total: Rp 0</p>
      <p class="text-neutral-500 mb-6" id="modalChange">Kembalian: Rp 0</p>
      <div class="flex gap-3">
        <button onclick="closeSuccessModal()" class="flex-1 py-3 px-5 rounded-xl border border-neutral-200 text-neutral-700 font-semibold hover:bg-neutral-50 transition-all">Tutup</button>
        <button onclick="printReceipt()" class="flex-1 py-3 px-5 rounded-xl bg-emerald-500 text-white font-semibold hover:bg-emerald-600 transition-all flex items-center justify-center gap-2">
          <iconify-icon icon="solar:printer-minimalistic-bold" width="18"></iconify-icon> Cetak Struk
        </button>
      </div>
    </div>
  </div>

  <!-- ====== MAIN LAYOUT ====== -->
  <div class="flex h-screen">

<aside class="w-[72px] bg-white border-r border-neutral-200/60 flex flex-col items-center py-4 gap-1 shrink-0 z-30">
  <div class="w-11 h-11 bg-emerald-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/25">
    <iconify-icon icon="solar:shop-bold" width="22" class="text-white"></iconify-icon>
  </div>
  <a href="dashboard.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center" title="Kasir">
    <iconify-icon icon="solar:calculator-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="produk.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Produk">
    <iconify-icon icon="solar:box-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="laporan.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Laporan">
    <iconify-icon icon="solar:chart-bold" width="22"></iconify-icon>
  </a>
  <a href="riwayat.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Riwayat">
    <iconify-icon icon="solar:clock-circle-bold" width="22"></iconify-icon>
  </a>
  <a href="absensi.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Absensi">
    <iconify-icon icon="solar:calendar-mark-bold" width="22"></iconify-icon>
  </a>
  <a href="stok.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Stok">
    <iconify-icon icon="solar:layers-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="pesan.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 relative" title="Pesan">
    <iconify-icon icon="solar:letter-bold" width="22"></iconify-icon>
    <span id="sidebarBadge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span>
  </a>
  <a href="chat.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 relative" title="Chat CS">
    <iconify-icon icon="solar:chat-round-dots-bold" width="22"></iconify-icon>
    <span id="sidebarChatBadge" class="hidden absolute top-0 right-0 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span>
  </a>
  <div class="flex-1"></div>
  <a href="/api/auth_logout.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 hover:text-red-500" title="Logout">
    <iconify-icon icon="solar:logout-2-bold" width="22"></iconify-icon>
  </a>
  <a href="settings.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Pengaturan">
    <iconify-icon icon="solar:settings-bold" width="22"></iconify-icon>
  </a>
</aside>

    <!-- Content -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Navbar -->
      <nav class="h-16 bg-white/80 backdrop-blur-xl border-b border-neutral-200/60 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-3">
          <h1 class="text-lg font-bold text-neutral-900 tracking-tight">FIRAJAYA</h1>
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">POS</span>
          <span id="dbStatus" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-400">Connecting...</span>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right hidden sm:block">
            <p class="text-xs text-neutral-400" id="currentDate"></p>
            <p class="text-sm font-semibold text-neutral-700" id="currentTime"></p>
          </div>
          <div class="w-px h-8 bg-neutral-200 hidden sm:block"></div>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
              <iconify-icon icon="solar:user-bold" width="18" class="text-emerald-500"></iconify-icon>
            </div>
          <div class="hidden sm:block">
            <p class="text-sm font-semibold text-neutral-800"><?php echo htmlspecialchars($userName); ?></p>
            <p class="text-[11px] text-neutral-400"><?php echo $userRole; ?></p>
          </div>
          </div>
        </div>
      </nav>

      <!-- Content Area -->
      <div class="flex-1 flex overflow-hidden">

        <!-- LEFT: Products -->
        <main class="flex-1 overflow-y-auto p-5 space-y-4">

          <!-- Search + Add Button Row -->
          <div class="flex gap-3">
            <div class="relative flex-1">
              <iconify-icon icon="solar:magnifer-linear" width="20" class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
              <input type="text" id="searchInput" placeholder="Cari produk" class="w-full pl-12 pr-4 py-3.5 bg-white rounded-2xl border border-neutral-200/80 text-sm text-neutral-800 placeholder:text-neutral-400 shadow-sm" oninput="onSearchDebounce()">
              <div class="absolute right-3 top-1/2 -translate-y-1/2 bg-neutral-100 rounded-lg px-2 py-1">
                <iconify-icon icon="solar:barcode-bold" width="18" class="text-neutral-400"></iconify-icon>
              </div>
            </div>
            <!-- TOMBOL TAMBAH PRODUK -->
            <button onclick="openAddProductModal()" class="shrink-0 flex items-center gap-2 px-5 py-3.5 bg-emerald-500 text-white font-semibold text-sm rounded-2xl shadow-lg shadow-emerald-500/20 hover:bg-emerald-600 hover:shadow-emerald-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all">
              <iconify-icon icon="solar:add-square-bold" width="20"></iconify-icon>
              <span class="hidden sm:inline">Tambah Produk</span>
            </button>
          </div>

          <!-- Categories -->
          <div id="categoryContainer" class="flex gap-2 overflow-x-auto pb-1"></div>

          <!-- Product Grid -->
          <div id="productGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3"></div>
        </main>

        <!-- RIGHT: Cart -->
        <aside id="cartPanel" class="w-[380px] bg-white/90 backdrop-blur-xl border-l border-neutral-200/60 flex flex-col shrink-0 z-10 max-lg:fixed max-lg:right-0 max-lg:top-0 max-lg:bottom-0 max-lg:w-full max-lg:max-w-[400px] max-lg:shadow-2xl max-lg:translate-x-full transition-transform duration-300">
          <!-- Cart Header -->
          <div class="p-5 pb-3 border-b border-neutral-100">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                  <iconify-icon icon="solar:cart-large-2-bold" width="20" class="text-emerald-500"></iconify-icon>
                </div>
                <div>
                  <h2 class="text-base font-bold text-neutral-900">Keranjang</h2>
                  <p class="text-xs text-neutral-400" id="cartCount">0 item</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <button onclick="clearCart()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:text-red-400 hover:bg-red-50 transition-all" title="Kosongkan">
                  <iconify-icon icon="solar:trash-bin-minimalistic-linear" width="18"></iconify-icon>
                </button>
                <button onclick="toggleCart()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:bg-neutral-100 transition-all lg:hidden">
                  <iconify-icon icon="solar:close-circle-linear" width="20"></iconify-icon>
                </button>
              </div>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="flex-1 overflow-y-auto p-4 space-y-2" id="cartItems">
            <div class="flex flex-col items-center justify-center h-full text-center py-10">
              <div class="w-20 h-20 bg-neutral-100 rounded-2xl flex items-center justify-center mb-4">
                <iconify-icon icon="solar:cart-large-2-linear" width="36" class="text-neutral-300"></iconify-icon>
              </div>
              <p class="text-sm font-semibold text-neutral-400 mb-1">Keranjang kosong</p>
              <p class="text-xs text-neutral-300">Tambahkan produk untuk mulai transaksi</p>
            </div>
          </div>

          <!-- Cart Summary -->
          <div class="border-t border-neutral-100 p-5 space-y-3 bg-white/50">
            <div class="flex justify-between text-sm">
              <span class="text-neutral-500">Subtotal</span>
              <span class="font-semibold text-neutral-700" id="subtotalDisplay">Rp 0</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-2">
                <span class="text-neutral-500">Diskon</span>
                <input type="number" id="discountInput" value="0" min="0" max="100" class="w-12 text-center text-xs bg-neutral-100 rounded-lg py-1 border border-transparent" oninput="updateTotals()">
                <span class="text-xs text-neutral-400">%</span>
              </div>
              <span class="font-semibold text-red-400" id="discountDisplay">- Rp 0</span>
            </div>
            <div class="flex justify-between text-sm">
              <div class="flex items-center gap-1">
                <span class="text-neutral-500">Pajak</span>
                <span class="text-[10px] bg-neutral-100 text-neutral-400 px-1.5 py-0.5 rounded font-semibold" id="taxLabel">11%</span>
              </div>
              <span class="font-semibold text-neutral-700" id="taxDisplay">Rp 0</span>
            </div>
            <div class="border-t border-dashed border-neutral-200 my-1"></div>
            <div class="flex justify-between items-end">
              <span class="text-base font-bold text-neutral-800">Total</span>
              <span class="text-2xl font-bold text-emerald-600 tracking-tight" id="totalDisplay">Rp 0</span>
            </div>
            <div class="relative mt-2">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-neutral-400">Rp</span>
              <input type="number" id="payInput" placeholder="0" class="w-full pl-12 pr-4 py-3.5 bg-neutral-50 rounded-xl border border-neutral-200/80 text-base font-bold text-neutral-800 placeholder:text-neutral-300 placeholder:font-normal" oninput="updateChange()">
            </div>
            <div class="flex justify-between items-center px-1" id="changeRow" style="display:none">
              <span class="text-sm text-neutral-500">Kembalian</span>
              <span class="text-lg font-bold" id="changeDisplay">Rp 0</span>
            </div>
            <button onclick="checkout()" id="checkoutBtn" class="checkout-btn w-full py-4 rounded-2xl text-white font-bold text-base flex items-center justify-center gap-2 mt-2" disabled>
              <iconify-icon icon="solar:card-bold" width="20"></iconify-icon>
              Bayar Sekarang
            </button>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <!-- Mobile Cart Toggle -->
  <button onclick="toggleCart()" id="mobileCartBtn" class="lg:hidden fixed bottom-6 right-6 z-40 w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-500/30 text-white">
    <iconify-icon icon="solar:cart-large-2-bold" width="24"></iconify-icon>
    <span id="mobileCartBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-[10px] font-bold flex items-center justify-center hidden">0</span>
  </button>

  <script src="/js/app.js"></script>
  <script>
    async function loadUnreadBadge() {
      try {
        const res = await fetch('/api/get_unread_count.php?_t=' + Date.now());
        const json = await res.json();
        const badge = document.getElementById('sidebarBadge');
        if (json.success && json.unread_count > 0) {
          badge.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
          badge.classList.remove('hidden');
        } else if (badge) {
          badge.classList.add('hidden');
        }
      } catch (e) { /* abaikan kalau gagal, badge tetap hidden */ }
    }
    loadUnreadBadge();

    async function loadChatBadge() {
      try {
        const res = await fetch('/api/get_chat_unread_count.php?_t=' + Date.now());
        const json = await res.json();
        const badge = document.getElementById('sidebarChatBadge');
        if (json.success && json.unread_count > 0) {
          badge.textContent = json.unread_count > 99 ? '99+' : json.unread_count;
          badge.classList.remove('hidden');
        } else if (badge) {
          badge.classList.add('hidden');
        }
      } catch (e) { /* abaikan kalau gagal, badge tetap hidden */ }
    }
    loadChatBadge();
  </script>
</body>
</html>