<?php
session_start();

$file_data = "taekwondo_data.txt";
if(!file_exists($file_data)) file_put_contents($file_data, json_encode([]));
$data_all = json_decode(file_get_contents($file_data), true);

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];

if(!isset($data_all[$username])) $data_all[$username] = ['lari'=>[]];

// TAMBAH LATIHAN LARI
if(isset($_POST['tambah_lari'])){
    $tanggal = $_POST['tanggal'];
    $waktu_lari = $_POST['waktu_lari'];
    $id = count($data_all[$username]['lari']) > 0 ? max(array_column($data_all[$username]['lari'],'id_lari'))+1 : 1;
    $data_all[$username]['lari'][] = ['id_lari'=>$id,'tanggal'=>$tanggal,'waktu_lari'=>$waktu_lari];
    file_put_contents($file_data, json_encode($data_all, JSON_PRETTY_PRINT));
    header("Location: index.php");
    exit;
}

// RESET DATA LARI (melalui AJAX)
if(isset($_POST['reset_data'])){
    $data_all[$username]['lari'] = [];
    file_put_contents($file_data, json_encode($data_all, JSON_PRETTY_PRINT));
    echo json_encode(['success'=>true]);
    exit;
}

$lari = $data_all[$username]['lari'];

// ANALISA TREND
$trend = '';
if(count($lari) >= 2){
    $last = floatval(str_replace(',', '.', $lari[count($lari)-1]['waktu_lari']));
    $prev = floatval(str_replace(',', '.', $lari[count($lari)-2]['waktu_lari']));
    if($last < $prev) $trend = "Waktu lari membaik ⬆ (lebih cepat dari latihan sebelumnya)";
    elseif($last > $prev) $trend = "Waktu lari menurun ⬇ (lebih lambat dari latihan sebelumnya)";
    else $trend = "Waktu lari stabil →";
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>BGFC - Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {
    background: linear-gradient(135deg,#000 0%,#4b0000 50%,#8b0000 100%);
    font-family:'Poppins',sans-serif;
    color:#fff;
    margin:0;
    min-height:100vh;
    display:flex;
    flex-direction:column;
}
.navbar {
    background: linear-gradient(90deg,#000,#8b0000);
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px 5vw;
    flex-wrap:wrap;
    box-shadow:0 4px 12px rgba(255,0,0,0.4);
    border-bottom: 2px solid rgba(255,0,0,0.5);
}
.navbar-left {display:flex; align-items:center; gap:10px; flex-wrap:wrap;}
.navbar-left img {display:block; max-height:50px;}
.navbar-right {display:flex; align-items:center; gap:10px; flex-wrap:wrap;}
.navbar-right span {font-weight:bold; font-size:16px;}
.navbar-right a {
    text-decoration:none;
    padding:6px 14px;
    border-radius:6px;
    font-weight:bold;
    background: linear-gradient(90deg,#ff0000,#660000);
    color:#fff;
    transition:0.3s;
}
.navbar-right a:hover {opacity:0.85;}
.container {
    background-color: rgba(20,20,20,0.85);
    padding:25px;
    border-radius:12px;
    box-shadow:0 0 25px rgba(255,0,0,0.3);
    width:100%;
    max-width:none;
    margin:20px 0;
}
/* Typography */
h5,h6 {color:#ff4444; text-shadow:0 0 6px rgba(255,0,0,0.3); margin-bottom:15px;}
/* Card */
.card {
    background-color: rgba(25,25,25,0.95);
    border-radius:12px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 4px 15px rgba(255,0,0,0.2);
}
/* Input Fields */
input, select {
    background:#1c1c1c!important;
    color:#fff!important;
    border:1px solid #ff0000!important;
    border-radius:6px;
    padding:8px 12px;
    transition:0.3s;
}
input:focus, select:focus {border-color:#ff5555!important; outline:none;}
/* Buttons */
.btn-primary, .btn-success {
    border:none;
    border-radius:6px;
    padding:8px 16px;
    font-weight:bold;
    transition:0.3s;
}
.btn-primary:hover, .btn-success:hover {opacity:0.85;}
/* Grafik */
canvas {background:#fff; border-radius:10px; padding:10px; width:100% !important; height:300px !important;}
p.trend {font-weight:bold; font-size:14px; color:#ffcc00;}
/* Flex layout for form & chart */
.flex-row {display:flex; gap:20px; flex-wrap:wrap;}
.flex-left {flex:1;}
.flex-right {flex:2;}
/* Keterangan interaktif */
.legend-box {
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:10px;
}
.legend-color {
    width:20px;
    height:20px;
    border-radius:4px;
    display:inline-block;
}
/* Footer */
footer {
    text-align:center;
    padding:12px;
    background: rgba(20,20,20,0.85);
    border-top:2px solid rgba(255,0,0,0.5);
    color:#ffcc00;
    margin-top:auto;
}
/* Responsive */
@media(max-width:768px){
    .navbar {flex-direction:column; align-items:flex-start; gap:8px;}
    .navbar-left {gap:8px;}
    .navbar-right {align-self:flex-end;}
    .flex-row {flex-direction:column;}
    canvas {height:250px !important;}
}
@media(max-width:480px){
    canvas {height:220px !important;}
}
</style>
</head>
<body>

<nav class="navbar navbar-dark">
  <div class="navbar-left">
    <img src="img/taekwondo indonesia.png" height="50" class="me-2">
    <img src="img/logo bgfc.png" height="50" class="me-2">
    <img src="img/tulisan bgfc.png" height="50">
  </div>
  <div class="navbar-right">
    <span>Selamat Datang, <?= htmlspecialchars($username) ?></span>
    <a href="login.php?logout=1" class="btn btn-primary" id="logoutBtn">Logout</a>
  </div>
</nav>

<div class="container mt-4">
<h5 class="mb-3">Dashboard Latihan</h5>

<div class="flex-row">
  <div class="flex-left card">
    <h6>Input Latihan Lari</h6>
    <form method="post">
        <input type="date" name="tanggal" class="form-control mb-2" required>
        <input type="text" name="waktu_lari" class="form-control mb-2" placeholder="Format 21.11" required>
        <button type="submit" name="tambah_lari" class="btn btn-success w-100">✔ Simpan</button>
    </form>
  </div>

  <div class="flex-right card">
    <h6>Grafik Waktu Lari (menit.detik)</h6>
    <canvas id="chartLari"></canvas>
    <?php if($trend): ?><p class="trend mt-2"><?= $trend ?></p><?php endif; ?>
    <div class="legend-box">
        <span class="legend-color" style="background:red;"></span> Waktu Lari
    </div>
    <div class="legend-box">
        <span class="legend-color" style="background:#ffcc00;"></span> Trend Latihan
    </div>
    <div class="mt-2 d-flex flex-column gap-2">
        <button id="downloadBtn" class="btn btn-primary w-100">Download Grafik</button>
        <button id="resetBtn" class="btn btn-success w-100">Reset Grafik & Data</button>
    </div>
  </div>
</div>
</div>

<footer>
&copy; <?= date('Y') ?> BGFC. All rights reserved.
</footer>

<script>
const lariLabels = <?= json_encode(array_column($lari,'tanggal')) ?>;
const lariValues = <?= json_encode(array_column($lari,'waktu_lari')) ?>;

let chartLari = new Chart(document.getElementById('chartLari'), {
    type:'line',
    data:{
        labels:lariLabels,
        datasets:[{
            label:'Waktu Lari',
            data:lariValues,
            borderColor:'red',
            backgroundColor:'rgba(255,0,0,0.3)',
            fill:false,
            tension:0.3
        }]
    },
    options:{responsive:true, maintainAspectRatio:false, scales:{y:{reverse:true}}}
});

// DOWNLOAD GRAFIK
document.getElementById('downloadBtn').addEventListener('click', function(){
    const link = document.createElement('a');
    link.href = chartLari.toBase64Image();
    link.download = 'grafik_lari_<?= $username ?>.png';
    link.click();
});

// RESET GRAFIK & DATA dengan konfirmasi
document.getElementById('resetBtn').addEventListener('click', function(){
    if(confirm("Apakah Anda yakin ingin mereset grafik dan menghapus semua data latihan?")){
        // Reset di front-end
        chartLari.destroy();
        chartLari = new Chart(document.getElementById('chartLari'), {
            type:'line',
            data:{labels:[], datasets:[{label:'Waktu Lari', data:[], borderColor:'red', backgroundColor:'rgba(255,0,0,0.3)', fill:false, tension:0.3}]},
            options:{responsive:true, maintainAspectRatio:false, scales:{y:{reverse:true}}}
        });

        // Kirim request AJAX ke PHP untuk reset data file
        fetch('index.php', {
            method: 'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'reset_data=1'
        }).then(res=>res.json()).then(data=>{
            if(data.success) alert('Data berhasil dihapus!');
        });
    }
});

// LOGOUT dengan konfirmasi
document.getElementById('logoutBtn').addEventListener('click', function(e){
    e.preventDefault();
    if(confirm("Apakah Anda yakin ingin logout?")){
        window.location.href = this.href;
    }
});
</script>
</body>
</html>
