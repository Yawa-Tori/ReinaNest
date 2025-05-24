<?php
include 'admin/koneksi.php';
session_start();

if (!isset($_SESSION['id_user'])) {
  echo json_encode(["success" => false, "message" => "silakan login terlebih dahulu"]);
  exit;
}

$id_user = $_SESSION['id_user'];
$tgl_jual = date('y-m-d H:i:s');

//ambil semua pesanan pengguna
$query_pesanan = "SELECT * FROM tb_pesanan WHERE id_user = '$id_user'";
$result_pesanan = mysqli_query($koneksi, $query_pesanan);

if (mysqli_num_rows($result_pesanan) == 0) {
  echo json_encode(["success" => false, "message" => "keranjang kosong!"]);
  exit;
}

//hitung subtotal dan simpan data pesanan
$subtotal = 0;
$pesanan_data = [];
while ($row = mysqli_fetch_assoc($result_pesanan)) {
  $pesanan_data[] = $row;
  $subtotal += $row['total'];
}

//cek stok semua produk sebelum melanjutkan
foreach ($pesanan_data as $pesanan) {
  $id_produk = mysqli_real_escape_string($koneksi, $pesanan['id_produk']);
  $qty = intval($pesanan['qty']);

  $query_stok = "SELECT stok FROM tb_produk WHERE id_produk = '$id_produk'";
  $result_stok = mysqli_query($koneksi, $query_stok);
  $row_stok = mysqli_fetch_assoc($result_stok);

  if (!$row_stok || $row_stok['stok'] < $qty) {
    echo json_encode(["success" => false, "message" => "stok tidak cukup untuk produk ID: $id_produk"]);
    exit;
  }
}

//hitung diskon
$diskon = 0;
if ($subtotal > 700000 && $subtotal <= 1500000) {
  $diskon = 0.05 * $subtotal;
} elseif ($subtotal > 1500000) {
  $diskon = 0.08 * $subtotal;
}

$total_bayar = $subtotal - $diskon;

//genearate id jual otomstis
$query_id = "SELECT MAX(id_jual) AS last_id FROM tb_jual";
$result_id = mysqli_query($koneksi, $query_id);
$row_id = mysqli_fetch_assoc($result_id);
$last_id = $row_id['last_id'];

if ($last_id) {
  $new_id = 'T' . str_pad((intval(substr($last_id, 1)) + 1), 3, '0', STR_PAD_LEFT);
} else {
  $new_id = 'T001';
}

//insert ke tb_jual
$query_jual = "INSERT INTO tb_jual (id_jual, id_user, tg_jual, total, diskon)
               VALUES ('$new_id', '$id_user', '$tgl_jual', '$total_bayar', '$diskon')";
if (!mysqli_query($koneksi, $query_jual)) {
  die(json_encode(["success" => false, "message" => "Gagal insert ke tb_jual: " . mysqli_error($koneksi)]));
}

//insert ke tb_jualdtl
$values = [];
foreach ($pesanan_data as $pesanan) {
  $id_produk = mysqli_real_escape_string($koneksi, $pesanan['id_produk']);
  $qty = intval($pesanan['qty']);
  $harga = floatval($pesanan['total']);

  $values[] = "('$new_id', '$id_produk', '$qty', '$harga')";
}
if (!empty($values)) {
  $query_jualdtl = "INSERT INTO tb_jualdtl (id_jual, id_produk, qty, harga)
                    VALUES " . implode(", ", $values);
                    if (!mysqli_query($koneksi, $query_jualdtl)) {
                      die(json_encode(["success" => false, "message" => "Gagal insert ke tb_jualdtl: " . mysqli_error($koneksi)]));
                    }
}

//kurangi stok produk
foreach ($pesanan_data as $pesanan) {
 $id_produk = mysqli_real_escape_string($koneksi, $pesanan['id_produk']);
 $qty = intval($pesanan['qty']);
 
 $query_update_stok = "UPDATE tb_produk SET stok = stok - $qty WHERE id_produk = '$id_produk'";

 if (!mysqli_query($koneksi, $query_update_stok)) {
  die(json_encode(["succes" => false, "message" => "Gagal update stok produk: " . mysqli_error($koneksi)]));
 }
}

//hapus data dari semua tb_pesanan
$query_hapus = "DELETE FROM tb_pesanan WHERE id_user = '$id_user'";
if (!mysqli_query($koneksi, $query_hapus)) {
  die(json_encode(["success" => false, "message" => "Gagal hapus tb_pesanan: " . mysqli_error($koneksi)]));
}

echo json_encode(["success" => true]);
?>