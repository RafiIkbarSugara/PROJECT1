// ============================================
// KasirKu POS — Main Application (REVISED)
// ============================================

// =================== CONFIG ===================
const API_BASE  = '';           // Kosong = root. Isi '/kasirku' jika di subfolder
const MAX_IMG   = 2 * 1024 * 1024; // 2 MB

// =================== STATE ===================
let allProducts     = [];
let categories      = [];
let cart            = [];
let currentCategory = 'all';
let searchTimer     = null;
let lastTransaction = null;
let dbConnected     = false;
let appSettings     = { nama_toko: 'FIRAJAYA', alamat: '', telepon: '', pajak_persen: 11 };

// =================== API HELPERS ===================

async function apiFetch(url, opts = {}) {
    try {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(API_BASE + url + sep + '_t=' + Date.now(), opts);
        // Selalu coba baca body JSON dulu, walau status bukan 200 — server
        // kita mengirim pesan error yang berguna di body (mis. 400/404/500),
        // jadi jangan dibuang sebelum dibaca.
        const json = await res.json().catch(() => null);
        if (json) return json;
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return { success: false, message: 'Response tidak valid' };
    } catch (e) {
        return { success: false, message: e.message };
    }
}

async function fetchProducts(kategori = null, search = null) {
    let url = '/api/get_products.php?';
    if (kategori && kategori !== 'all') url += `kategori=${kategori}&`;
    if (search) url += `search=${encodeURIComponent(search)}&`;

    const json = await apiFetch(url);
    if (json.success) { setDbStatus(true); return json.data; }

    setDbStatus(false);
    return [];
}

async function fetchCategories() {
    const json = await apiFetch('/api/get_categories.php');
    if (json.success) { setDbStatus(true); return json.data; }
    setDbStatus(false);
    return [];
}

async function fetchSettings() {
    const json = await apiFetch('/api/get_settings.php');
    if (json.success) {
        appSettings = json.data;
        // Update nama toko di navbar secara dinamis
        const navTitle = document.querySelector('nav h1');
        if (navTitle) navTitle.textContent = appSettings.nama_toko || 'POS';
        // Update label pajak di keranjang
        const taxLbl = document.getElementById('taxLabel');
        if (taxLbl) taxLbl.textContent = (appSettings.pajak_persen || 0) + '%';
    }
}

async function submitAddProduct(formData) {
    try {
        const res = await fetch(API_BASE + '/api/add_product.php', {
            method: 'POST',
            body: formData
        });
        return await res.json();
    } catch (e) {
        return { success: false, message: e.message };
    }
}

async function submitDeleteProduct(id) {
    return await apiFetch(`/api/delete_product.php?id=${id}`, { method: 'DELETE' });
}

async function submitCheckout(data) {
    try {
        const res = await fetch(API_BASE + '/api/process_checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.success) setDbStatus(true);
        else setDbStatus(false);
        return json;
    } catch (e) {
        // Gagal terhubung ke server — JANGAN anggap transaksi berhasil.
        // Stok & catatan transaksi tidak boleh "lolos" tanpa benar-benar
        // tersimpan di database, jadi kita kembalikan status gagal yang jelas.
        setDbStatus(false);
        return {
            success: false,
            message: 'Tidak dapat terhubung ke server. Transaksi belum tersimpan — periksa koneksi/server lalu coba lagi.'
        };
    }
}

function setDbStatus(connected) {
    dbConnected = connected;
    const el = document.getElementById('dbStatus');
    if (!el) return;
    if (connected) {
        el.textContent = 'KASIR';
        el.className = 'text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600';
    } else {
        el.textContent = 'KASIR';
        el.className = 'text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-600';
    }
}

// =================== INIT ===================

async function init() {
    await fetchSettings(); 
    showSkeleton();
    categories = await fetchCategories();
    renderCategories();
    allProducts = await fetchProducts();
    renderProducts(allProducts);
    updateAll();
}

function showSkeleton() {
    const grid = document.getElementById('productGrid');
    if (!grid) return;
    let html = '';
    for (let i = 0; i < 10; i++) {
        html += `<div class="bg-white rounded-2xl border border-neutral-200/60 overflow-hidden">
            <div class="aspect-square skeleton"></div>
            <div class="p-3 space-y-2">
                <div class="h-4 skeleton rounded w-3/4"></div>
                <div class="h-3 skeleton rounded w-1/2"></div>
                <div class="flex justify-between items-center">
                    <div class="h-5 skeleton rounded w-1/3"></div>
                    <div class="h-8 w-8 skeleton rounded-xl"></div>
                </div>
            </div>
        </div>`;
    }
    grid.innerHTML = html;
}

// =================== RENDER CATEGORIES ===================

function renderCategories() {
    const container = document.getElementById('categoryContainer');
    if (!container) return;
    container.innerHTML = `<button class="cat-btn active whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold border border-transparent transition-all duration-200" data-category="all" onclick="setCategory('all',this)">Semua</button>`;

    categories.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'cat-btn whitespace-nowrap px-4 py-2.5 rounded-xl text-sm font-semibold bg-white text-neutral-600 border border-neutral-200/80 hover:border-emerald-300 transition-all duration-200';
        btn.dataset.category = cat.id_kategori;
        btn.onclick = function() { setCategory(cat.id_kategori, this); };
        btn.innerHTML = `${cat.nama_kategori}`;
        container.appendChild(btn);
    });
}

// =================== RENDER PRODUCTS ===================

function renderProducts(products) {
    const grid = document.getElementById('productGrid');
    if (!grid) return;
    grid.innerHTML = '';

    if (!products || products.length === 0) {
        grid.innerHTML = `<div class="col-span-full flex flex-col items-center justify-center py-20 text-center">
            <iconify-icon icon="solar:box-minimalistic-linear" width="48" class="text-neutral-300 mb-3"></iconify-icon>
            <p class="text-sm font-semibold text-neutral-400">Produk tidak ditemukan</p>
        </div>`;
        return;
    }

    products.forEach((p, i) => {
        const inCart    = cart.find(c => c.id_produk === p.id_produk);
        const stokCls  = p.stok <= 10 ? 'stok-low' : 'stok-ok';
        const isOut    = p.stok <= 0;
        const imgUrl   = p.gambar_url || `assets/img/firajaya.png`;
        const safeName = (p.nama_produk || '').replace(/'/g, "\\'");  
        const card = document.createElement('div');
        card.className = 'product-card';
        card.style.animationDelay = `${i * 35}ms`;
        card.innerHTML = `
        <div class="product-card-inner bg-white rounded-2xl border border-neutral-200/60 overflow-hidden group relative ${isOut ? 'opacity-50' : ''}">
            <div class="relative aspect-square bg-neutral-100 overflow-hidden">
                <img src="${imgUrl}" alt="${p.nama_produk}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="w-full h-full items-center justify-center text-5xl text-neutral-300 hidden"><iconify-icon icon="solar:box-minimalistic-bold" width="48"></iconify-icon></div>
                <span class="absolute top-2 left-2 text-[10px] font-bold uppercase tracking-wider bg-white/90 backdrop-blur-sm px-2 py-1 rounded-lg text-neutral-500">${p.nama_kategori || ''}</span>
                ${inCart ? `<div class="absolute top-2 right-2 w-6 h-6 bg-emerald-500 rounded-lg flex items-center justify-center text-white text-xs font-bold anim-cart-bounce">${inCart.qty}</div>` : ''}
                ${isOut ? '<div class="absolute inset-0 bg-white/60 flex items-center justify-center"><span class="text-xs font-bold text-red-500 bg-white px-3 py-1 rounded-lg shadow">Habis</span></div>' : ''}
             <!-- Quick action buttons -->
            <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200">
                <button onclick="openEditProductModal(${p.id_produk})"
                class="w-7 h-7 rounded-lg bg-white/80 backdrop-blur-sm flex items-center justify-center text-neutral-400 hover:text-blue-500 hover:bg-blue-50 transition-all"
                title="Edit produk">
                <iconify-icon icon="solar:pen-linear" width="14"></iconify-icon>
                </button>
                <button onclick="confirmDeleteProduct(${p.id_produk},'${safeName}')"
                class="w-7 h-7 rounded-lg bg-white/80 backdrop-blur-sm flex items-center justify-center text-neutral-400 hover:text-red-500 hover:bg-red-50 transition-all"
                title="Hapus produk">
                <iconify-icon icon="solar:trash-bin-minimalistic-linear" width="14"></iconify-icon>
                </button>
            </div>
            </div>
            <div class="p-3">
                <h3 class="text-sm font-semibold text-neutral-800 truncate mb-0.5">${p.nama_produk}</h3>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded ${stokCls}">Stok: ${p.stok}</span>
                    <span class="text-[11px] text-neutral-400">/ ${p.satuan || 'pcs'}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-base font-bold text-emerald-600">Rp ${Number(p.harga).toLocaleString('id-ID')}</span>
                    <div class="flex items-center gap-1.5">
                        <button onclick="openRestockModal(${p.id_produk}, '${safeName}', ${p.stok})"
                            class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center hover:bg-amber-100 transition-all"
                            title="Tambah Stok">
                            <iconify-icon icon="solar:box-minimalistic-bold" width="18"></iconify-icon>
                        </button>
                        <button onclick="addToCart(${p.id_produk})" class="add-btn w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center" ${isOut ? 'disabled' : ''}>
                            <iconify-icon icon="solar:add-circle-bold" width="20"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
        grid.appendChild(card);
    });
}

// =================== ADD PRODUCT MODAL ===================

function openAddProductModal() {
    const modal = document.getElementById('addProductModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    const sel = document.getElementById('formKategori');
    sel.innerHTML = '<option value="">— Pilih Kategori —</option>';
    categories.forEach(c => {
        sel.innerHTML += `<option value="${c.id_kategori}">${c.nama_kategori}</option>`;
    });
    document.getElementById('addProductForm').reset();
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('imgPreviewContainer').classList.remove('has-image');
    document.getElementById('addProductBtn').disabled = false;
    document.getElementById('addProductBtn').innerHTML = '<iconify-icon icon="solar:diskette-bold" width="18"></iconify-icon> Simpan Produk';
}

function closeAddProductModal() {
    const modal = document.getElementById('addProductModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function previewImage(input) {
    const preview = document.getElementById('imgPreview');
    const container = document.getElementById('imgPreviewContainer');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > MAX_IMG) {
            showToast('Ukuran gambar maksimal 2 MB', 'error');
            input.value = '';
            preview.style.display = 'none';
            container.classList.remove('has-image');
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            container.classList.add('has-image');
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        container.classList.remove('has-image');
    }
}

async function handleAddProduct(e) {
    e.preventDefault();
    const form     = document.getElementById('addProductForm');
    const formData = new FormData(form);
    const btn      = document.getElementById('addProductBtn');

    const nama = formData.get('nama_produk')?.trim();
    const deskripsi = formData.get('deskripsi')?.trim();
    const harga = parseInt(formData.get('harga'));
    const stok  = parseInt(formData.get('stok'));
    const kategori = formData.get('id_kategori');

    if (!nama) { showToast('Nama produk wajib diisi', 'error'); return; }
    if (!deskripsi) { showToast('Deskripsi produk wajib diisi', 'error'); return; }
    if (!harga || harga <= 0) { showToast('Harga harus lebih dari 0', 'error'); return; }
    if (isNaN(stok) || stok < 0) { showToast('Stok tidak valid', 'error'); return; }
    if (!kategori) { showToast('Kategori wajib dipilih', 'error'); return; }

    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="18" class="anim-spin"></iconify-icon> Menyimpan...';

    try {
        const result = await submitAddProduct(formData);
        if (result.success) {
            showToast(result.message || 'Produk berhasil ditambahkan!', 'success');
            closeAddProductModal();
            allProducts = await fetchProducts(
                currentCategory !== 'all' ? currentCategory : null,
                document.getElementById('searchInput')?.value.trim() || null
            );
            renderProducts(allProducts);
            updateAll();
        } else {
            showToast(result.message || 'Gagal menambahkan produk', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="solar:diskette-bold" width="18"></iconify-icon> Simpan Produk';
    }
}

// =================== DELETE PRODUCT ===================

let pendingDeleteId   = null;
let pendingDeleteName = '';

function confirmDeleteProduct(id, name) {
    pendingDeleteId   = id;
    pendingDeleteName = name;
    document.getElementById('deleteProductName').textContent = name;
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    pendingDeleteId = null;
}

async function executeDeleteProduct() {
    if (!pendingDeleteId) return;
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="16" class="anim-spin"></iconify-icon> Menghapus...';

    try {
        const result = await submitDeleteProduct(pendingDeleteId);
        if (result.success) {
            showToast('Produk berhasil dihapus', 'success');
            cart = cart.filter(c => c.id_produk !== pendingDeleteId);
            allProducts = await fetchProducts();
            renderProducts(allProducts);
            updateAll();
        } else {
            showToast(result.message || 'Gagal menghapus produk', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Ya, Hapus';
        closeDeleteModal();
    }
}

// =================== RESTOCK ===================

let restockProductId   = null;
let restockProductStok = 0;

function openRestockModal(id_produk, nama_produk, current_stok) {
    restockProductId   = id_produk;
    restockProductStok = parseInt(current_stok) || 0;

    document.getElementById('restockProductName').textContent  = nama_produk || '-';
    document.getElementById('restockCurrentStok').textContent  = restockProductStok;
    document.getElementById('restockInput').value              = '';
    document.getElementById('restockNewStok').textContent      = restockProductStok;

    const modal = document.getElementById('restockModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    setTimeout(() => {
        const input = document.getElementById('restockInput');
        if (input) input.focus();
    }, 100);
}

function closeRestockModal() {
    const modal = document.getElementById('restockModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    restockProductId = null;
}

function updateRestockPreview() {
    const tambah  = parseInt(document.getElementById('restockInput').value) || 0;
    const newStok = restockProductStok + tambah;
    document.getElementById('restockNewStok').textContent = newStok;
}

async function submitRestock() {
    if (!restockProductId) return;
    const tambah = parseInt(document.getElementById('restockInput').value) || 0;
    if (tambah <= 0) {
        showToast('Jumlah stok harus lebih dari 0', 'error');
        return;
    }

    const btn = document.getElementById('restockSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';

    try {
        const res = await fetch(API_BASE + '/api/update_stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_produk: restockProductId,
                tambah_stok: tambah
            })
        });
        const result = await res.json();

        if (result.success) {
            showToast(result.message, 'success');
            closeRestockModal();
            allProducts = await fetchProducts();
            renderProducts(allProducts);
            updateAll();
        } else {
            showToast(result.message || 'Gagal update stok', 'error');
        }
    } catch (e) {
        showToast('Error: ' + e.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" width="16"></iconify-icon> Tambah';
    }
}

// =================== EDIT PRODUCT ===================

let editingProductId = null;
let editingProductImage = null;

async function openEditProductModal(id_produk) {
    const product = findProduct(id_produk);
    if (!product) { showToast('Produk tidak ditemukan', 'error'); return; }

    editingProductId = id_produk;
    editingProductImage = product.gambar || null;

    document.getElementById('editIdProduk').value = product.id_produk;
    document.getElementById('editNama').value     = product.nama_produk;
    document.getElementById('editDeskripsi').value = product.deskripsi || '';
    document.getElementById('editHarga').value    = product.harga;
    document.getElementById('editStok').value     = product.stok;
    document.getElementById('editBarcode').value  = product.barcode || '';
    document.getElementById('editSatuan').value   = product.satuan || 'pcs';
    document.getElementById('editHapusGambar').value = '0';

    const sel = document.getElementById('editKategori');
    sel.innerHTML = '<option value="">— Pilih —</option>';
    categories.forEach(c => {
        const selected = c.id_kategori == product.id_kategori ? 'selected' : '';
        sel.innerHTML += `<option value="${c.id_kategori}" ${selected}>${c.nama_kategori}</option>`;
    });

    const imgWrap    = document.getElementById('editImgPreviewWrap');
    const imgPreview = document.getElementById('editImgPreview');
    if (product.gambar_url) {
        imgPreview.src = product.gambar_url;
        imgWrap.classList.remove('hidden');
    } else {
        imgWrap.classList.add('hidden');
    }

    document.getElementById('editGambar').value = '';
    document.getElementById('editProductBtn').disabled = false;
    document.getElementById('editProductBtn').innerHTML = '<iconify-icon icon="solar:pen-bold" width="18"></iconify-icon> Simpan Perubahan';

    const modal = document.getElementById('editProductModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => document.getElementById('editNama').focus(), 100);
}

function closeEditProductModal() {
    const modal = document.getElementById('editProductModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    editingProductId = null;
}

function removeEditImage() {
    document.getElementById('editImgPreviewWrap').classList.add('hidden');
    document.getElementById('editGambar').value = '';
    document.getElementById('editHapusGambar').value = '1';
}

async function handleEditProduct(e) {
    e.preventDefault();
    if (!editingProductId) return;

    const form     = document.getElementById('editProductForm');
    const formData = new FormData(form);
    const btn      = document.getElementById('editProductBtn');

    const nama = formData.get('nama_produk')?.trim();
    const harga = parseInt(formData.get('harga'));
    const stok  = parseInt(formData.get('stok'));
    const kategori = formData.get('id_kategori');

    if (!nama) { showToast('Nama produk wajib diisi', 'error'); return; }
    if (!harga || harga <= 0) { showToast('Harga harus lebih dari 0', 'error'); return; }
    if (isNaN(stok) || stok < 0) { showToast('Stok tidak valid', 'error'); return; }
    if (!kategori) { showToast('Kategori wajib dipilih', 'error'); return; }

    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="18" class="anim-spin"></iconify-icon> Menyimpan...';

    try {
        const res = await fetch(API_BASE + '/api/update_product.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            showToast(result.message || 'Produk berhasil diperbarui!', 'success');
            closeEditProductModal();
            allProducts = await fetchProducts(
                currentCategory !== 'all' ? currentCategory : null,
                document.getElementById('searchInput')?.value.trim() || null
            );
            renderProducts(allProducts);
            updateAll();
        } else {
            showToast(result.message || 'Gagal update produk', 'error');
        }
    } catch (err) {
        showToast('Error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="solar:pen-bold" width="18"></iconify-icon> Simpan Perubahan';
    }
}

// =================== CART OPERATIONS ===================

function findProduct(id) {  
    return allProducts.find(p => p.id_produk === id);
}

function addToCart(id_produk) {
    const product = findProduct(id_produk);
    if (!product) return;
    if (product.stok <= 0) { showToast('Stok habis!', 'error'); return; }

    const existing = cart.find(c => c.id_produk === id_produk);
    if (existing) {
        if (existing.qty >= product.stok) {
            showToast('Stok tidak mencukupi!', 'error'); return;
        }
        existing.qty++;
    } else {
        cart.push({
            id_produk: product.id_produk,
            nama_produk: product.nama_produk,
            harga: product.harga,
            satuan: product.satuan || 'pcs',
            qty: 1,
            max_stok: product.stok
        });
    }
    updateAll();
    showToast(`${product.nama_produk} ditambahkan`, 'success');
}

function incrementQty(id_produk) {
    const item = cart.find(c => c.id_produk === id_produk);
    if (!item) return;
    if (item.qty >= item.max_stok) { showToast('Stok tidak mencukupi!', 'error'); return; }
    item.qty++;
    updateAll();
}

function decrementQty(id_produk) {
    const item = cart.find(c => c.id_produk === id_produk);
    if (!item) return;
    if (item.qty > 1) { item.qty--; }
    else { cart = cart.filter(c => c.id_produk !== id_produk); }
    updateAll();
}

function deleteFromCart(id_produk) {
    const item = cart.find(c => c.id_produk === id_produk);
    cart = cart.filter(c => c.id_produk !== id_produk);
    updateAll();
    if (item) showToast(`${item.nama_produk} dihapus`, 'error');
}

function clearCart() {
    if (cart.length === 0) return;
    cart = [];
    document.getElementById('discountInput').value = 0;
    document.getElementById('payInput').value = '';
    updateAll();
    showToast('Keranjang dikosongkan', 'error');
}

// =================== UPDATE ALL ===================

function updateAll() {
    renderCartItems();
    updateTotals();
    renderProducts(getFilteredProducts());
    updateMobileBadge();
}

function getFilteredProducts() {
    let filtered = [...allProducts];
    const search = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
    if (currentCategory !== 'all') filtered = filtered.filter(p => p.id_kategori == currentCategory);
    if (search) filtered = filtered.filter(p => p.nama_produk.toLowerCase().includes(search) || (p.barcode && p.barcode.includes(search)));
    return filtered;
}

function getCartEmoji(name) {
    if (name.includes('Beras'))   return '🍚';
    if (name.includes('Minyak'))  return '🛢️';
    if (name.includes('Gula'))    return '🍬';
    if (name.includes('Tepung'))  return '🌾';
    if (name.includes('Mie'))     return '🍜';
    if (name.includes('Telur'))   return '🥚';
    if (name.includes('Kopi'))    return '☕';
    if (name.includes('Air'))     return '💧';
    if (name.includes('Teh'))     return '🍵';
    if (name.includes('Susu'))    return '🥛';
    if (name.includes('Kecap') || name.includes('Saos')) return '🫙';
    if (name.includes('Garam'))   return '🧂';
    if (name.includes('Bawang'))  return '🧅';
    if (name.includes('Roti'))    return '🍞';
    if (name.includes('Kerupuk')) return '🦐';
    if (name.includes('Sabun'))   return '🧼';
    return '📦';
}

function renderCartItems() {
    const container = document.getElementById('cartItems');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-center py-10">
            <div class="w-20 h-20 bg-neutral-100 rounded-2xl flex items-center justify-center mb-4">
                <iconify-icon icon="solar:cart-large-2-linear" width="36" class="text-neutral-300"></iconify-icon>
            </div>
            <p class="text-sm font-semibold text-neutral-400 mb-1">Keranjang kosong</p>
            <p class="text-xs text-neutral-300">Tambahkan produk untuk mulai transaksi</p>
        </div>`;
        document.getElementById('cartCount').textContent = '0 item';
        return;
    }

    const totalItems = cart.reduce((s, c) => s + c.qty, 0);
    document.getElementById('cartCount').textContent = `${totalItems} item`;

    container.innerHTML = '';
    cart.forEach((item, i) => {
        const div = document.createElement('div');
        div.className = 'cart-item bg-neutral-50 rounded-xl p-3 flex gap-3 items-center';
        div.style.animationDelay = `${i * 25}ms`;
        div.innerHTML = `
            <div class="w-11 h-11 rounded-xl bg-emerald-50 shrink-0 flex items-center justify-center text-lg">${getCartEmoji(item.nama_produk)}</div>
            <div class="flex-1 min-w-0">
                <h4 class="text-[13px] font-semibold text-neutral-800 truncate">${item.nama_produk}</h4>
                <p class="text-[11px] text-neutral-400">Rp ${Number(item.harga).toLocaleString('id-ID')}/${item.satuan}</p>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="decrementQty(${item.id_produk})" class="qty-btn w-6 h-6 rounded-md bg-white border border-neutral-200 flex items-center justify-center text-neutral-500 text-xs font-bold">−</button>
                <span class="w-6 text-center text-sm font-bold text-neutral-800">${item.qty}</span>
                <button onclick="incrementQty(${item.id_produk})" class="qty-btn w-6 h-6 rounded-md bg-white border border-neutral-200 flex items-center justify-center text-neutral-500 text-xs font-bold">+</button>
            </div>
            <div class="text-right shrink-0 ml-1 min-w-[70px]">
                <p class="text-sm font-bold text-neutral-800">Rp ${(item.harga * item.qty).toLocaleString('id-ID')}</p>
                <button onclick="deleteFromCart(${item.id_produk})" class="text-[10px] text-red-400 hover:text-red-600 transition-colors">hapus</button>
            </div>`;
        container.appendChild(div);
    });
}

// =================== TOTALS ===================

function calcTotals() {
    const subtotal = cart.reduce((s, c) => s + (c.harga * c.qty), 0);
    const discPct  = Math.min(100, Math.max(0, parseFloat(document.getElementById('discountInput').value) || 0));
    const discRp   = Math.round(subtotal * discPct / 100);
    const afterDisc = subtotal - discRp;
    const taxRp = Math.round(afterDisc * (parseFloat(appSettings.pajak_persen) || 0) / 100);
    const total    = afterDisc + taxRp;
    return { subtotal, discPct, discRp, afterDisc, taxRp, total };
}

function updateTotals() {
    const t = calcTotals();
    document.getElementById('subtotalDisplay').textContent  = `Rp ${t.subtotal.toLocaleString('id-ID')}`;
    document.getElementById('discountDisplay').textContent  = `- Rp ${t.discRp.toLocaleString('id-ID')}`;
    document.getElementById('taxDisplay').textContent       = `Rp ${t.taxRp.toLocaleString('id-ID')}`;
    document.getElementById('totalDisplay').textContent     = `Rp ${t.total.toLocaleString('id-ID')}`;
    document.getElementById('checkoutBtn').disabled         = cart.length === 0;
    updateChange();
}

function updateChange() {
    const t = calcTotals();
    const bayar = parseFloat(document.getElementById('payInput').value) || 0;
    const row   = document.getElementById('changeRow');
    const disp  = document.getElementById('changeDisplay');

    if (bayar > 0) {
        row.style.display = 'flex';
        const change = bayar - t.total;
        if (change >= 0) {
            disp.textContent = `Rp ${change.toLocaleString('id-ID')}`;
            disp.className = 'text-lg font-bold text-emerald-600';
        } else {
            disp.textContent = `- Rp ${Math.abs(change).toLocaleString('id-ID')}`;
            disp.className = 'text-lg font-bold text-red-500';
        }
    } else {
        row.style.display = 'none';
    }
}

// =================== CHECKOUT ===================

async function checkout() {
    if (cart.length === 0) return;
    const t = calcTotals();
    const bayar = parseFloat(document.getElementById('payInput').value) || 0;

    if (bayar < t.total) {
        showToast('Uang bayar kurang!', 'error');
        document.getElementById('payInput').focus();
        return;
    }

    const btn = document.getElementById('checkoutBtn');
    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="solar:refresh-bold" width="20" class="anim-spin"></iconify-icon> Memproses...';

    try {
        const payload = {
            items: cart.map(c => ({
                id_produk: c.id_produk, nama_produk: c.nama_produk,
                harga: c.harga, qty: c.qty
            })),
            diskon_persen: t.discPct, 
            pajak_persen: parseFloat(appSettings.pajak_persen) || 0, // FIX 1: Pajak dari settings
            bayar, 
            subtotal: t.subtotal, diskon_rupiah: t.discRp,
            pajak_rupiah: t.taxRp, total: t.total
        };

        const result = await submitCheckout(payload);

        if (!result.success) {
            showToast(result.message || 'Transaksi gagal disimpan', 'error');
            return;
        }

        lastTransaction = result;

        document.getElementById('modalNoTrx').textContent    = `No: ${result.no_transaksi}`;
        document.getElementById('modalTotal').textContent     = `Total: Rp ${t.total.toLocaleString('id-ID')}`;
        document.getElementById('modalChange').textContent    = `Kembalian: Rp ${result.kembalian.toLocaleString('id-ID')}`;

        const modal = document.getElementById('successModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        cart = [];
        document.getElementById('discountInput').value = 0;
        document.getElementById('payInput').value = '';

        allProducts = await fetchProducts();
        updateAll();

        showToast('Transaksi berhasil!', 'success');
    } catch (err) {
        showToast('Gagal: ' + err.message, 'error');
    } finally {
        btn.disabled = cart.length === 0;
        btn.innerHTML = '<iconify-icon icon="solar:card-bold" width="20"></iconify-icon> Bayar Sekarang';
    }
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('successModal').classList.remove('flex');
}

// =================== PRINT RECEIPT ===================

function printReceipt() {
    if (!lastTransaction) return;
    const tx = lastTransaction;
    const now = new Date();

    // FIX 2: Data toko dan kasir dinamis dari settings
    const storeName = appSettings.nama_toko || 'FIRAJAYA';
    const storeAddr = appSettings.alamat || '';
    const storePhone = appSettings.telepon || '';
    const kasirName = typeof KASIR_NAME !== 'undefined' ? KASIR_NAME : 'Kasir';

    let rows = '';
    cart.forEach(c => {
        rows += `<tr><td>${c.nama_produk}</td><td style="text-align:center">${c.qty}</td><td style="text-align:right">${(c.harga*c.qty).toLocaleString('id-ID')}</td></tr>`;
    });

    const el = document.getElementById('receiptPrint');
    el.innerHTML = `
        <div style="text-align:center;margin-bottom:8px">
            <strong style="font-size:14px">${storeName}</strong><br>
            <small>${storeAddr}</small><br>
            <small>${storePhone ? 'Telp: '+storePhone : ''}</small>
        </div>
        <hr style="border-top:1px dashed #000;margin:6px 0">
        <div><small>No: ${tx.no_transaksi}</small></div>
        <div><small>${now.toLocaleDateString('id-ID')} ${now.toLocaleTimeString('id-ID')}</small></div>
        <div><small>Kasir: ${kasirName}</small></div>
        <hr style="border-top:1px dashed #000;margin:6px 0">
        <table style="width:100%;font-size:11px"><thead><tr><th style="text-align:left">Item</th><th>Qty</th><th style="text-align:right">Sub</th></tr></thead><tbody>${rows}</tbody></table>
        <hr style="border-top:1px dashed #000;margin:6px 0">
        <div style="font-size:11px">
            <div style="display:flex;justify-content:space-between"><span>Subtotal</span><span>Rp ${(tx.subtotal||0).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between"><span>Diskon</span><span>Rp ${(tx.diskon_rupiah||0).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between"><span>Pajak</span><span>Rp ${(tx.pajak_rupiah||0).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:13px;margin-top:4px"><span>TOTAL</span><span>Rp ${(tx.total||0).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between"><span>Bayar</span><span>Rp ${(tx.bayar||0).toLocaleString('id-ID')}</span></div>
            <div style="display:flex;justify-content:space-between;font-weight:bold"><span>Kembalian</span><span>Rp ${(tx.kembalian||0).toLocaleString('id-ID')}</span></div>
        </div>
        <hr style="border-top:1px dashed #000;margin:6px 0">
        <div style="text-align:center;font-size:10px"><p>Terima kasih atas kunjungan Anda!</p><p>Barang yang sudah dibeli tidak ditukar</p></div>`;
    el.classList.remove('hidden');
    window.print();
    el.classList.add('hidden');
    closeSuccessModal();
}

// =================== CATEGORY & SEARCH ===================

async function setCategory(cat, btn) {
    currentCategory = cat;
    document.querySelectorAll('.cat-btn').forEach(b => {
        b.classList.remove('active');
        b.classList.add('bg-white','text-neutral-600','border','border-neutral-200/80');
    });
    btn.classList.add('active');
    btn.classList.remove('bg-white','text-neutral-600','border','border-neutral-200/80');

    const search = document.getElementById('searchInput')?.value.trim() || null;
    allProducts = await fetchProducts(cat !== 'all' ? cat : null, search);
    renderProducts(allProducts);
}

function onSearchDebounce() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        const search = document.getElementById('searchInput')?.value.trim() || null;
        allProducts = await fetchProducts(currentCategory !== 'all' ? currentCategory : null, search);
        renderProducts(allProducts);
    }, 300);
}

// =================== MOBILE ===================

function toggleCart() {
    const panel = document.getElementById('cartPanel');
    panel.classList.toggle('max-lg:translate-x-full');
    panel.classList.toggle('max-lg:translate-x-0');
}

function updateMobileBadge() {
    const badge = document.getElementById('mobileCartBadge');
    if (!badge) return;
    const n = cart.reduce((s, c) => s + c.qty, 0);
    if (n > 0) { badge.classList.remove('hidden'); badge.textContent = n > 99 ? '99+' : n; }
    else { badge.classList.add('hidden'); }
}

// =================== TOAST ===================

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const icon = type === 'success'
        ? '<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>'
        : '<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>';
    toast.className = 'toast-enter glass-strong flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]';
    toast.innerHTML = `${icon} ${message}`;
    container.appendChild(toast);
    setTimeout(() => { toast.classList.remove('toast-enter'); toast.classList.add('toast-exit'); setTimeout(() => toast.remove(), 300); }, 2500);
}

// =================== CLOCK ===================

function updateClock() {
    const now = new Date();
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const d = document.getElementById('currentDate');
    const t = document.getElementById('currentTime');
    if (d) d.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`;
    if (t) t.textContent = now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
}

// =================== BARCODE SCANNER ===================
let barcodeBuffer = '';
let barcodeTimer = null;

// FIX 3: Menggabungkan Keydown listener agar tidak konflik
document.addEventListener('keydown', function(e) {
    const activeEl = document.activeElement;
    const isTyping = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');
    const isModalInput = activeEl && (activeEl.id === 'restockInput' || activeEl.id === 'editNama' || activeEl.id === 'addProductForm');

    // --- BARCODE LOGIC ---
    if (e.key === 'Enter' && barcodeBuffer.length > 3 && !isModalInput) {
        e.preventDefault();
        processBarcodeScan(barcodeBuffer);
        barcodeBuffer = '';
        clearTimeout(barcodeTimer);
        return;
    }

    if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
        if (isTyping && activeEl.id !== 'searchInput') return;
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimer);
        barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 100);
    }

    // --- KEYBOARD SHORTCUTS ---
    if (e.key === 'F2') { e.preventDefault(); document.getElementById('searchInput')?.focus(); }
    if (e.key === 'F4' && !e.ctrlKey) { e.preventDefault(); checkout(); }
    
    if (e.key === 'Enter') {
        const deleteModal  = document.getElementById('deleteConfirmModal');
        const restockModal = document.getElementById('restockModal');

        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            e.preventDefault();
            executeDeleteProduct();
        } else if (restockModal && !restockModal.classList.contains('hidden')) {
            e.preventDefault();
            submitRestock();
        }
    }
    
    if (e.key === 'Escape') {
        closeAddProductModal();
        closeEditProductModal();
        closeDeleteModal();
        closeRestockModal();
        closeSuccessModal();
        const panel = document.getElementById('cartPanel');
        if (panel && !panel.classList.contains('max-lg:translate-x-full')) toggleCart();
    }
});

function processBarcodeScan(barcode) {
    const product = allProducts.find(p => p.barcode === barcode);
    if (product) {
        addToCart(product.id_produk);
        showToast(`Scanner: ${product.nama_produk}`, 'success');
        const searchInput = document.getElementById('searchInput');
        if (searchInput) searchInput.value = '';
    } else {
        showToast(`Barcode "${barcode}" tidak ditemukan`, 'error');
    }
}

// =================== START ===================
document.addEventListener('DOMContentLoaded', () => {
    init();
    updateClock();
    setInterval(updateClock, 1000);
});