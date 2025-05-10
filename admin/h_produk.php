<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
  $id_produk = $_GET['id'];

  //ambil dara gambar produk
  $query = mysqli_query($koneksi, "SELECT * FROM tb_produk WHERE id_produk='$id_produk'");
  $data = mysqli_fetch_array($query);

  if ($data) {
    $gambar = $data['gambar'];
    $dir = "produk_img/";

    //hapus gambar jika ada
    if (!empty($gambar) && file_exists($dir . $gambar)) {
      unlink($dir . $gambar);
    }

    //hapus data produk
    $delete = mysqli_query($koneksi, "DELETE FROM tb_produk WHERE id_produk='$id_produk'");

    if ($delete) {
      echo "<script>alert('Data produk berhasil dihapus!'); window.location='produk.php';</script>";
      exit;
    } else {
      echo "<script>alert('Gagal menghapus data produk!'); window.location='produk.php';</script>";
      exit;
    }
  } else {
    echo "<script>alert('Data produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
  }
} else {
  echo "<script>alert('Akses tidak valid!'); window.location='produk.php';</script>";
  exit;
}
?>