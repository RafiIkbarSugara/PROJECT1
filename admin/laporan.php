<?php
require_once __DIR__ . '/_guard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FIRAJAYA — Laporan Penjualan</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
  <style>
    .stat-card { transition: all .2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.08); }
    .quick-btn { transition: all .15s ease; }
    .quick-btn:hover { background: #059669; color: white; border-color: #059669; }
    .quick-btn.active { background: #059669; color: white; border-color: #059669; }
    input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; }
    input[type="date"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }
    .rank-badge {
      width: 28px; height: 28px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 700;
    }
    .rank-1 { background: #FEF3C7; color: #D97706; }
    .rank-2 { background: #F3F4F6; color: #6B7280; }
    .rank-3 { background: #FED7AA; color: #C2410C; }
    .rank-other { background: #F0F7F1; color: #64906E; }
    .cat-bar { transition: width 0.8s cubic-bezier(0.16,1,0.3,1); }
  </style>
</head>
<body class="bg-neutral-100 h-screen overflow-hidden">

  <!-- Toast Container -->
  <div id="toastContainer" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

  <!-- MAIN LAYOUT -->
  <div class="flex h-screen">

<aside class="w-[72px] bg-white border-r border-neutral-200/60 flex flex-col items-center py-4 gap-1 shrink-0 z-30">
  <div class="w-11 h-11 bg-emerald-500 rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-500/25">
    <iconify-icon icon="solar:shop-bold" width="22" class="text-white"></iconify-icon>
  </div>
  <a href="dashboard.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Kasir">
    <iconify-icon icon="solar:calculator-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="produk.php" class="sidebar-icon w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400" title="Produk">
    <iconify-icon icon="solar:box-minimalistic-bold" width="22"></iconify-icon>
  </a>
  <a href="laporan.php" class="sidebar-icon active w-11 h-11 rounded-xl flex items-center justify-center" title="Laporan">
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
          <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Laporan</span>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right hidden sm:block">
            <p class="text-xs text-neutral-400" id="currentDate"></p>
            <p class="text-sm font-semibold text-neutral-700" id="currentTime"></p>
          </div>
        </div>
      </nav>

      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-5 space-y-4">

        <!-- Quick Filter -->
        <div class="flex flex-wrap gap-2">
          <button onclick="quickFilter('today')" id="btnToday" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:sun-bold" width="16"></iconify-icon> Hari Ini
          </button>
          <button onclick="quickFilter('yesterday')" id="btnYesterday" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:moon-bold" width="16"></iconify-icon> Kemarin
          </button>
          <button onclick="quickFilter('week')" id="btnWeek" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:calendar-bold" width="16"></iconify-icon> 7 Hari
          </button>
          <button onclick="quickFilter('month')" id="btnMonth" class="quick-btn active px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:calendar-minimalistic-bold" width="16"></iconify-icon> 30 Hari
          </button>
          <button onclick="quickFilter('all')" id="btnAll" class="quick-btn px-4 py-2 rounded-xl bg-white border border-neutral-200/80 text-sm font-semibold text-neutral-600 flex items-center gap-1.5">
            <iconify-icon icon="solar:inbox-bold" width="16"></iconify-icon> Semua
          </button>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-neutral-200/60 p-4 shadow-sm">
          <div class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[150px]">
              <label class="block text-xs font-semibold text-neutral-500 mb-1.5">Dari Tanggal</label>
              <input type="date" id="dariFilter" class="w-full px-3 py-2.5 bg-neutral-50 rounded-xl border border-neutral-200 text-sm cursor-pointer" onchange="onDateChange()">
            </div>
            <div class="min-w-[150px]">
              <label class="block text-xs font-semibold text-neutral-500 mb-1.5">Sampai Tanggal</label>
              <input type="date" id="sampaiFilter" class="w-full px-3 py-2.5 bg-neutral-50 rounded-xl border border-neutral-200 text-sm cursor-pointer" onchange="onDateChange()">
            </div>
            <button onclick="clearFilters()" class="px-4 py-2.5 rounded-xl bg-neutral-100 border border-neutral-200 text-sm font-semibold text-neutral-600 hover:bg-neutral-200 transition-all flex items-center gap-1.5">
              <iconify-icon icon="solar:refresh-linear" width="16"></iconify-icon> Reset
            </button>
            <button onclick="exportReportPDF()" id="exportPdfBtn" class="px-4 py-2.5 rounded-xl bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 transition-all flex items-center gap-1.5">
              <iconify-icon icon="solar:file-download-bold" width="16"></iconify-icon> Export PDF
            </button>
          </div>
          <div id="filterInfo" class="hidden mt-3 pt-3 border-t border-neutral-100 flex items-center gap-2">
            <iconify-icon icon="solar:filter-bold" width="14" class="text-emerald-500"></iconify-icon>
            <span class="text-xs text-neutral-500" id="filterText"></span>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:wallet-bold" width="16" class="text-emerald-500"></iconify-icon></div>
              <span class="text-xs text-neutral-400">Pendapatan</span>
            </div>
            <p class="text-xl font-bold text-emerald-600" id="sumRevenue">Rp 0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:document-text-bold" width="16" class="text-blue-500"></iconify-icon></div>
              <span class="text-xs text-neutral-400">Transaksi</span>
            </div>
            <p class="text-xl font-bold text-blue-600" id="sumTrx">0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:box-minimalistic-bold" width="16" class="text-amber-500"></iconify-icon></div>
              <span class="text-xs text-neutral-400">Item Terjual</span>
            </div>
            <p class="text-xl font-bold text-amber-600" id="sumItems">0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:chart-2-bold" width="16" class="text-purple-500"></iconify-icon></div>
              <span class="text-xs text-neutral-400">Rata-rata</span>
            </div>
            <p class="text-xl font-bold text-purple-600" id="sumAvg">Rp 0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:tag-price-bold" width="16" class="text-red-400"></iconify-icon></div>
              <span class="text-xs text-neutral-400">Diskon</span>
            </div>
            <p class="text-xl font-bold text-red-400" id="sumDiskon">Rp 0</p>
          </div>
          <div class="stat-card bg-white rounded-2xl border border-neutral-200/60 p-4">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-8 h-8 bg-cyan-50 rounded-lg flex items-center justify-center"><iconify-icon icon="solar:bill-list-bold" width="16" class="text-cyan-500"></iconify-icon></div>
              <span class="text-xs text-neutral-400">Pajak</span>
            </div>
            <p class="text-xl font-bold text-cyan-600" id="sumPajak">Rp 0</p>
          </div>
        </div>

        <!-- Chart + Best Products -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

          <!-- Sales Chart -->
          <div class="lg:col-span-2 bg-white rounded-2xl border border-neutral-200/60 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-bold text-neutral-800 flex items-center gap-2">
                <iconify-icon icon="solar:chart-bold" width="18" class="text-emerald-500"></iconify-icon>
                Grafik Penjualan
              </h3>
              <span class="text-xs text-neutral-400" id="chartPeriod">30 hari terakhir</span>
            </div>
            <div class="relative" style="height:280px">
              <canvas id="salesChart"></canvas>
            </div>
          </div>

          <!-- Best Products -->
          <div class="bg-white rounded-2xl border border-neutral-200/60 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4 gap-2 flex-wrap">
              <h3 class="text-sm font-bold text-neutral-800 flex items-center gap-2">
                <iconify-icon icon="solar:fire-bold" width="18" class="text-amber-500"></iconify-icon>
                Produk Terlaris
              </h3>
              <select id="bestProductsKategoriFilter" onchange="loadReport()" class="text-xs font-medium border border-neutral-200 rounded-lg px-2.5 py-1.5 bg-neutral-50 text-neutral-600 outline-none focus:ring-1 focus:ring-emerald-400">
                <option value="all">Semua Kategori</option>
              </select>
            </div>
            <div class="space-y-3" id="bestProductsList">
              <div class="text-center py-8 text-neutral-300">
                <iconify-icon icon="solar:fire-linear" width="32" class="mb-2"></iconify-icon>
                <p class="text-xs">Memuat data...</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Category Sales -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

          <!-- Category Bar Chart -->
          <div class="bg-white rounded-2xl border border-neutral-200/60 p-5 shadow-sm">
            <h3 class="text-sm font-bold text-neutral-800 flex items-center gap-2 mb-4">
              <iconify-icon icon="solar:pie-chart-2-bold" width="18" class="text-purple-500"></iconify-icon>
              Penjualan per Kategori
            </h3>
            <div class="relative" style="height:250px">
              <canvas id="categoryChart"></canvas>
            </div>
          </div>

          <!-- Category Detail -->
          <div class="bg-white rounded-2xl border border-neutral-200/60 p-5 shadow-sm">
            <h3 class="text-sm font-bold text-neutral-800 flex items-center gap-2 mb-4">
              <iconify-icon icon="solar:layers-minimalistic-bold" width="18" class="text-emerald-500"></iconify-icon>
              Detail Kategori
            </h3>
            <div class="space-y-3" id="categoryDetailList">
              <div class="text-center py-8 text-neutral-300">
                <iconify-icon icon="solar:layers-minimalistic-linear" width="32" class="mb-2"></iconify-icon>
                <p class="text-xs">Memuat data...</p>
              </div>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script>
    const API_BASE = '';
    let salesChart = null;
    let categoryChart = null;
    let activeQuickFilter = 'month';

    // =================== DATE HELPERS ===================
    function fmt(d){const y=d.getFullYear(),m=String(d.getMonth()+1).padStart(2,'0'),dd=String(d.getDate()).padStart(2,'0');return `${y}-${m}-${dd}`}
    function today(){return fmt(new Date())}
    function yesterday(){const d=new Date();d.setDate(d.getDate()-1);return fmt(d)}
    function daysAgo(n){const d=new Date();d.setDate(d.getDate()-n+1);return fmt(d)}

    // =================== API ===================
    async function apiFetch(url){
      try{const r=await fetch(API_BASE+url+((url.includes('?')?'&':'?')+'_t='+Date.now()));const json=await r.json().catch(()=>null);if(json)return json;if(!r.ok)throw new Error('HTTP '+r.status);return{success:false,message:'Response tidak valid'};}
      catch(e){return{success:false,message:e.message}}
    }

    // =================== QUICK FILTER ===================
    function quickFilter(type){
      activeQuickFilter=type;
      document.querySelectorAll('.quick-btn').forEach(b=>b.classList.remove('active'));
      const dari=document.getElementById('dariFilter'),sampai=document.getElementById('sampaiFilter');
      switch(type){
        case 'today': dari.value=today();sampai.value=today();document.getElementById('btnToday').classList.add('active');break;
        case 'yesterday': dari.value=yesterday();sampai.value=yesterday();document.getElementById('btnYesterday').classList.add('active');break;
        case 'week': dari.value=daysAgo(7);sampai.value=today();document.getElementById('btnWeek').classList.add('active');break;
        case 'month': dari.value=daysAgo(30);sampai.value=today();document.getElementById('btnMonth').classList.add('active');break;
        case 'all': dari.value='';sampai.value='';document.getElementById('btnAll').classList.add('active');break;
      }
      updateFilterInfo();loadReport();
    }
    function onDateChange(){document.querySelectorAll('.quick-btn').forEach(b=>b.classList.remove('active'));activeQuickFilter='custom';updateFilterInfo();loadReport();}
    function updateFilterInfo(){
      const dari=document.getElementById('dariFilter').value,sampai=document.getElementById('sampaiFilter').value,info=document.getElementById('filterInfo'),txt=document.getElementById('filterText');
      let parts=[];
      if(dari&&sampai){parts.push(`${new Date(dari).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})} — ${new Date(sampai).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})}`)}
      if(parts.length>0){txt.textContent='Periode: '+parts.join(' | ');info.classList.remove('hidden')}
      else{info.classList.add('hidden')}
    }
    function clearFilters(){document.getElementById('dariFilter').value='';document.getElementById('sampaiFilter').value='';activeQuickFilter='all';document.querySelectorAll('.quick-btn').forEach(b=>b.classList.remove('active'));document.getElementById('btnAll').classList.add('active');document.getElementById('filterInfo').classList.add('hidden');loadReport();}

    // =================== LOAD REPORT ===================
    let lastReportData = null;

    async function loadReport(){
      const dari=document.getElementById('dariFilter').value,sampai=document.getElementById('sampaiFilter').value;
      let url='/api/get_sales_report.php?';
      if(dari)url+=`dari=${dari}&`;if(sampai)url+=`sampai=${sampai}&`;
      const kategori=document.getElementById('bestProductsKategoriFilter').value;
      if(kategori&&kategori!=='all')url+=`kategori=${kategori}&`;
      const json=await apiFetch(url);
      if(json.success){lastReportData=json.data;renderSummary(json.data.summary);renderChart(json.data.chart);renderBestProducts(json.data.best_products,json.data.summary.total_pendapatan);renderCategoryChart(json.data.category_sales);renderCategoryDetail(json.data.category_sales,json.data.summary.total_pendapatan);}
      else{showToast('Gagal memuat laporan','error')}
    }

    // =================== SUMMARY ===================
    function renderSummary(s){
      document.getElementById('sumRevenue').textContent=`Rp ${Number(s.total_pendapatan).toLocaleString('id-ID')}`;
      document.getElementById('sumTrx').textContent=s.total_transaksi;
      document.getElementById('sumItems').textContent=Number(s.total_item).toLocaleString('id-ID');
      document.getElementById('sumAvg').textContent=`Rp ${Number(s.rata_rata).toLocaleString('id-ID')}`;
      document.getElementById('sumDiskon').textContent=`Rp ${Number(s.total_diskon).toLocaleString('id-ID')}`;
      document.getElementById('sumPajak').textContent=`Rp ${Number(s.total_pajak).toLocaleString('id-ID')}`;
    }

    // =================== CHART ===================
    function renderChart(data){
      const ctx=document.getElementById('salesChart').getContext('2d');
      if(salesChart)salesChart.destroy();
      const labels=data.map(d=>{const dt=new Date(d.tanggal);return dt.toLocaleDateString('id-ID',{day:'numeric',month:'short'})});
      const values=data.map(d=>Number(d.total_harian));
      const trxCount=data.map(d=>Number(d.jumlah_transaksi));

      salesChart=new Chart(ctx,{
        type:'bar',
        data:{
          labels,
          datasets:[
            {label:'Pendapatan (Rp)',data:values,backgroundColor:'rgba(5,150,105,0.7)',borderColor:'#059669',borderWidth:1,borderRadius:6,order:2,yAxisID:'y'},
            {label:'Jumlah Transaksi',data:trxCount,type:'line',borderColor:'#f59e0b',backgroundColor:'rgba(245,158,11,0.1)',borderWidth:2,pointRadius:3,pointBackgroundColor:'#f59e0b',tension:0.3,fill:true,order:1,yAxisID:'y1'}
          ]
        },
        options:{
          responsive:true,maintainAspectRatio:false,
          interaction:{mode:'index',intersect:false},
          plugins:{legend:{position:'top',labels:{usePointStyle:true,pointStyle:'circle',padding:16,font:{size:11,family:'Geist,Inter,sans-serif'}}},tooltip:{backgroundColor:'#171717',titleFont:{family:'Geist'},bodyFont:{family:'Geist'},padding:10,cornerRadius:8,callbacks:{label:function(c){return c.datasetIndex===0?'Rp '+c.raw.toLocaleString('id-ID'):c.raw+' transaksi'}}}},
          scales:{
            x:{grid:{display:false},ticks:{font:{size:10,family:'Geist'}}},
            y:{position:'left',grid:{color:'#f5f5f5'},ticks:{font:{size:10,family:'Geist'},callback:v=>'Rp '+(v/1000)+'k'}},
            y1:{position:'right',grid:{display:false},ticks:{font:{size:10,family:'Geist'},stepSize:1}}
          }
        }
      });

      document.getElementById('chartPeriod').textContent=labels.length>0?`${labels[0]} — ${labels[labels.length-1]}`:'Tidak ada data';
    }

    // =================== BEST PRODUCTS ===================
    function renderBestProducts(data,totalRevenue){
      const el=document.getElementById('bestProductsList');
      if(!data||data.length===0){el.innerHTML='<div class="text-center py-8 text-neutral-300"><iconify-icon icon="solar:fire-linear" width="32" class="mb-2"></iconify-icon><p class="text-xs">Belum ada data</p></div>';return}
      el.innerHTML='';
      data.forEach((p,i)=>{
        const rank=i<3?`rank-${i+1}`:'rank-other';
        const pct=totalRevenue>0?Math.round(p.total_penjualan/totalRevenue*100):0;
        el.innerHTML+=`
        <div class="flex items-center gap-3 py-2 ${i<data.length-1?'border-b border-neutral-100':''}">
          <div class="rank-badge ${rank}">${i+1}</div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-neutral-800 truncate">${p.nama_produk}</p>
            <div class="flex items-center gap-2 mt-1">
              <div class="flex-1 h-1.5 bg-neutral-100 rounded-full overflow-hidden">
                <div class="cat-bar h-full bg-emerald-400 rounded-full" style="width:${Math.min(pct,100)}%"></div>
              </div>
              <span class="text-[10px] font-semibold text-neutral-400">${pct}%</span>
            </div>
          </div>
          <div class="text-right shrink-0">
            <p class="text-xs font-bold text-neutral-800">${Number(p.total_qty).toLocaleString('id-ID')}</p>
            <p class="text-[10px] text-neutral-400">terjual</p>
          </div>
        </div>`;
      });
    }

    // =================== CATEGORY CHART ===================
    function renderCategoryChart(data){
      const ctx=document.getElementById('categoryChart').getContext('2d');
      if(categoryChart)categoryChart.destroy();
      if(!data||data.length===0){categoryChart=null;return}
      const colors=['#059669','#3b82f6','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#ec4899','#14b8a6','#6366f1'];
      categoryChart=new Chart(ctx,{
        type:'doughnut',
        data:{labels:data.map(c=>c.nama_kategori),datasets:[{data:data.map(c=>Number(c.total_penjualan)),backgroundColor:colors.slice(0,data.length),borderWidth:0,borderRadius:4,spacing:2}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'right',labels:{usePointStyle:true,pointStyle:'circle',padding:12,font:{size:11,family:'Geist'}}},tooltip:{backgroundColor:'#171717',cornerRadius:8,padding:10,callbacks:{label:c=>' Rp '+c.raw.toLocaleString('id-ID')}}}}
      });
    }

    // =================== CATEGORY DETAIL ===================
    function renderCategoryDetail(data,totalRevenue){
      const el=document.getElementById('categoryDetailList');
      if(!data||data.length===0){el.innerHTML='<div class="text-center py-8 text-neutral-300"><p class="text-xs">Belum ada data</p></div>';return}
      el.innerHTML='';
      const colors=['bg-emerald-500','bg-blue-500','bg-amber-500','bg-red-400','bg-purple-500','bg-cyan-500','bg-orange-500','bg-pink-400','bg-teal-500','bg-indigo-500'];
      data.forEach((c,i)=>{
        const pct=totalRevenue>0?Math.round(c.total_penjualan/totalRevenue*100):0;
        el.innerHTML+=`
        <div class="flex items-center gap-3">
          <div class="w-3 h-3 rounded-full ${colors[i%colors.length]} shrink-0"></div>
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center mb-1">
              <span class="text-sm font-semibold text-neutral-700">${c.nama_kategori}</span>
              <span class="text-xs font-bold text-neutral-600">${pct}%</span>
            </div>
            <div class="h-1.5 bg-neutral-100 rounded-full overflow-hidden">
              <div class="cat-bar h-full ${colors[i%colors.length]} rounded-full" style="width:${pct}%"></div>
            </div>
            <div class="flex justify-between mt-1">
              <span class="text-[10px] text-neutral-400">${Number(c.total_qty).toLocaleString('id-ID')} item</span>
              <span class="text-[10px] font-semibold text-neutral-500">Rp ${Number(c.total_penjualan).toLocaleString('id-ID')}</span>
            </div>
          </div>
        </div>`;
      });
    }

    // =================== EXPORT PDF ===================
    async function exportReportPDF(){
      if(!lastReportData){ showToast('Data laporan belum siap, coba lagi sebentar','error'); return; }

      const btn=document.getElementById('exportPdfBtn');
      const originalHtml=btn.innerHTML;
      btn.disabled=true;
      btn.innerHTML='<iconify-icon icon="solar:refresh-bold" width="16" class="animate-spin"></iconify-icon> Membuat PDF...';

      try{
        const { jsPDF }=window.jspdf;
        const doc=new jsPDF({unit:'mm',format:'a4'});
        const pageWidth=210, margin=15;
        let y=18;

        const dari=document.getElementById('dariFilter').value;
        const sampai=document.getElementById('sampaiFilter').value;
        const periodeText=(dari||sampai)?`${dari||'...'} s/d ${sampai||'...'}`:document.getElementById('chartPeriod').textContent;

        // ----- Header -----
        doc.setFont('helvetica','bold'); doc.setFontSize(16); doc.setTextColor(5,150,105);
        doc.text('FIRAJAYA', margin, y);
        doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(120,120,120);
        doc.text('Laporan Penjualan', margin, y+6);
        doc.text('Periode: '+periodeText, margin, y+11);
        doc.text('Dicetak: '+new Date().toLocaleString('id-ID'), pageWidth-margin, y+11, {align:'right'});
        y+=18;
        doc.setDrawColor(230,230,230); doc.line(margin,y,pageWidth-margin,y); y+=8;

        // ----- Ringkasan -----
        const s=lastReportData.summary;
        doc.setFont('helvetica','bold'); doc.setFontSize(12); doc.setTextColor(30,30,30);
        doc.text('Ringkasan', margin, y); y+=7;

        const summaryRows=[
          ['Total Pendapatan', 'Rp '+Number(s.total_pendapatan).toLocaleString('id-ID')],
          ['Total Transaksi', String(s.total_transaksi)],
          ['Total Item Terjual', Number(s.total_item).toLocaleString('id-ID')],
          ['Rata-rata / Transaksi', 'Rp '+Number(s.rata_rata).toLocaleString('id-ID')],
          ['Total Diskon', 'Rp '+Number(s.total_diskon).toLocaleString('id-ID')],
          ['Total Pajak', 'Rp '+Number(s.total_pajak).toLocaleString('id-ID')],
        ];
        doc.setFont('helvetica','normal'); doc.setFontSize(10);
        const colWidth=(pageWidth-margin*2)/2;
        summaryRows.forEach((row,i)=>{
          const col=i%2, rowY=y+Math.floor(i/2)*7;
          const x=margin+col*colWidth;
          doc.setTextColor(120,120,120); doc.text(row[0]+':', x, rowY);
          doc.setTextColor(30,30,30); doc.setFont('helvetica','bold');
          doc.text(row[1], x+38, rowY);
          doc.setFont('helvetica','normal');
        });
        y+=Math.ceil(summaryRows.length/2)*7+8;

        // ----- Grafik penjualan harian (gambar) -----
        const chartCanvas=document.getElementById('salesChart');
        if(chartCanvas){
          doc.setFont('helvetica','bold'); doc.setFontSize(12); doc.setTextColor(30,30,30);
          doc.text('Grafik Penjualan Harian', margin, y); y+=5;
          const chartImg=await html2canvas(chartCanvas,{scale:2,backgroundColor:'#ffffff'});
          const imgData=chartImg.toDataURL('image/png');
          const imgWidth=pageWidth-margin*2;
          const imgHeight=imgWidth*(chartCanvas.height/chartCanvas.width);
          doc.addImage(imgData,'PNG',margin,y,imgWidth,imgHeight);
          y+=imgHeight+10;
        }

        // Pindah halaman kalau sisa tempat sedikit
        if(y>230){ doc.addPage(); y=18; }

        // ----- Produk terlaris -----
        const bestProducts=lastReportData.best_products||[];
        const kategoriSelect=document.getElementById('bestProductsKategoriFilter');
        const kategoriLabel=kategoriSelect.value!=='all'?` (${kategoriSelect.options[kategoriSelect.selectedIndex].text.trim()})`:'';
        doc.setFont('helvetica','bold'); doc.setFontSize(12); doc.setTextColor(30,30,30);
        doc.text('Produk Terlaris'+kategoriLabel, margin, y); y+=7;

        if(bestProducts.length===0){
          doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(150,150,150);
          doc.text('Belum ada data', margin, y); y+=8;
        }else{
          doc.setFontSize(9);
          bestProducts.forEach((p,i)=>{
            if(y>275){ doc.addPage(); y=18; }
            doc.setTextColor(30,30,30); doc.setFont('helvetica','normal');
            doc.text(`${i+1}. ${p.nama_produk}`, margin, y);
            doc.setTextColor(100,100,100);
            doc.text(`${Number(p.total_qty).toLocaleString('id-ID')} terjual`, pageWidth-margin, y, {align:'right'});
            y+=6;
          });
          y+=6;
        }

        if(y>230){ doc.addPage(); y=18; }

        // ----- Penjualan per kategori -----
        const categorySales=lastReportData.category_sales||[];
        doc.setFont('helvetica','bold'); doc.setFontSize(12); doc.setTextColor(30,30,30);
        doc.text('Penjualan per Kategori', margin, y); y+=7;

        if(categorySales.length===0){
          doc.setFont('helvetica','normal'); doc.setFontSize(10); doc.setTextColor(150,150,150);
          doc.text('Belum ada data', margin, y); y+=8;
        }else{
          doc.setFontSize(9);
          categorySales.forEach(c=>{
            if(y>275){ doc.addPage(); y=18; }
            const pct=s.total_pendapatan>0?Math.round(c.total_penjualan/s.total_pendapatan*100):0;
            doc.setTextColor(30,30,30); doc.setFont('helvetica','normal');
            doc.text(`${c.nama_kategori}`, margin, y);
            doc.setTextColor(100,100,100);
            doc.text(`Rp ${Number(c.total_penjualan).toLocaleString('id-ID')} (${pct}%)`, pageWidth-margin, y, {align:'right'});
            y+=6;
          });
        }

        const fileName=`Laporan-FIRAJAYA-${dari||'semua'}_${sampai||'periode'}.pdf`;
        doc.save(fileName);
        showToast('PDF berhasil diunduh', 'success');

      }catch(err){
        console.error(err);
        showToast('Gagal membuat PDF: '+err.message, 'error');
      }finally{
        btn.disabled=false;
        btn.innerHTML=originalHtml;
      }
    }

    // =================== TOAST ===================
    function showToast(msg,type='success'){const c=document.getElementById('toastContainer'),t=document.createElement('div'),ic=type==='success'?'<iconify-icon icon="solar:check-circle-bold" width="18" class="text-emerald-500"></iconify-icon>':'<iconify-icon icon="solar:close-circle-bold" width="18" class="text-red-400"></iconify-icon>';t.className='toast-enter glass-strong flex items-center gap-2 px-4 py-3 rounded-xl border border-neutral-200/60 shadow-lg text-sm font-medium text-neutral-700 min-w-[220px]';t.innerHTML=`${ic} ${msg}`;c.appendChild(t);setTimeout(()=>{t.classList.remove('toast-enter');t.classList.add('toast-exit');setTimeout(()=>t.remove(),300)},2500)}

    // =================== CLOCK ===================
    function updateClock(){const n=new Date(),d=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],m=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];const dd=document.getElementById('currentDate'),tt=document.getElementById('currentTime');if(dd)dd.textContent=`${d[n.getDay()]}, ${n.getDate()} ${m[n.getMonth()]}`;if(tt)tt.textContent=n.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit',second:'2-digit'})}

    // =================== KATEGORI FILTER ===================
    async function loadCategoriesForFilter(){
      const json=await apiFetch('/api/get_categories.php');
      if(!json.success)return;
      const select=document.getElementById('bestProductsKategoriFilter');
      json.data.forEach(k=>{
        const opt=document.createElement('option');
        opt.value=k.id_kategori;
        opt.textContent=`${k.nama_kategori}`;
        select.appendChild(opt);
      });
    }

    // =================== INIT ===================
    loadCategoriesForFilter();
    quickFilter('month');
    updateClock();setInterval(updateClock,1000);

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