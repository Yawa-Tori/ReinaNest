<?php
include 'koneksi.php';

//perikasa apakah parametr id telah dikirimnkan
if (isset($_GET['id'])) {
  $id_user = $_GET['id'];

  //query untuk menghapus data berdasarkan id_user
  if ($query) {
    echo "<script>alert('Data pengguna berhasil dihapus'); window.location='pengguna.php';</script>";
    header("refresh:0; pengguna.php");
  } else {
    echo "<script>alert('Data pengguna gagal dihapus'); window.location='pengguna.php';</script>";
    header("refresh:0; pengguna.php");
  }
} else {
  echo "<script>alert('ID pengguna tidak ditemukan'); window.location='pengguna.php';</script>";
  header("refresh:0; pengguna.php");
}
?>