<?php
if(isset($_GET['id_product'])){
	$id_product=$_GET['id_product'];
}
else {
	die("Error. No ID Selected!");    
}
$nama=$_GET['nama_product'];
include "koneksi.php";
$tanggal1=$_GET['tanggal1'];
$tanggal2=$_GET['tanggal2'];
// QUERY
$result=mysqli_query($connection,
"SELECT 
topd.id_order, topd.id_product, topd.tanggal_diterima, topd.jumlah, tp.nama_distributor,
sum(tp.stok) total_stok,
COALESCE(topd.status, 0) AS keterangan,
COALESCE(topd.cabang, 0) AS cabang,
COALESCE(hotline, 0) AS hotline
FROM 
	(SELECT id_order, id_product, tanggal_diterima, cabang, status, jumlah 
	FROM vw_order_pusat_detil 
	WHERE id_product = '$id_product' AND tanggal_diterima BETWEEN '$tanggal1' AND '$tanggal2' 
	UNION 
	SELECT id_order, id_product, tanggal_retur, cabang, status, jumlah_retur 
	FROM tbl_retur_cabang 
	WHERE id_product = '$id_product' AND tanggal_retur BETWEEN '$tanggal1' AND '$tanggal2' AND status='retur diterima'
	UNION
	SELECT id_order, id_product, tanggal_order, cabang, status, jumlah 
	FROM tbl_order_cabang 
	WHERE id_product='$id_product' AND tanggal_diterima BETWEEN '$tanggal1 00:00:00' AND '$tanggal2 23:59:59' AND status='diterima' 
	UNION 
	SELECT id_order, id_product, tanggal_order, cabang, status, jumlah 
	FROM tbl_order_karyawan 
	WHERE id_product='$id_product' AND tanggal_order BETWEEN '$tanggal1 00:00:00' AND '$tanggal2 23:59:59' AND cabang='asem' AND status='selesai') 
AS topd
LEFT JOIN tbl_product tp ON topd.id_product = tp.id_product
LEFT JOIN tbl_retur_cabang trc ON trc.id_order = topd.id_order
LEFT JOIN vw_order_karyawan_hotline hl ON topd.id_order = hl.id_order
LEFT JOIN vw_order_pusat op ON topd.id_order = op.id_order
LEFT JOIN tbl_user u ON hl.id = u.id
where tp.stok >= 0
GROUP BY id_order
ORDER by id_order ASC");
$stok=mysqli_fetch_array($result);
?>
<title><?php echo $id_order; ?></title>
<style type="text/css" media="print">
    @page 
    {
        	size:  auto;
            -webkit-print-color-adjust:exact;
            margin-top: 0.6cm;
            margin-bottom: 0.6cm;
            margin-right: 1cm;
            margin-left: 1cm;

    }
    
    #printbtn {
        display :  none;
    }
 div {
       background: #fff !important;
           -webkit-print-color-adjust:exact;
    }
    th{
	border: 1px solid #000000;
   background-color: #DCDCDC;
   background-image: none;
   border-collapse: separate;
	}
	
</style>
<style type="text/css">
	*{
		font-family: Calibri;
	    -webkit-print-color-adjust:exact;
	    font-size: 12px;
	}
	th{
		border: 1px solid #000000;
   background-color: #DCDCDC;
   background-image: none;
   border-collapse: separate;
	}
	
</style>

<div style="width: 100%;border: 0px solid black;height: 110px;">
	<div style="width: 50%;float: left;">
			<img style="width: 200px;" src="logo_cop.jpg">
	</div>	
	<div style="width: 50%;float: right;font-family: Calibri;color: #A9A9A9;opacity: 85%;text-align: center;height: 110px;">
		<h2 style="font-size: 25px;margin-top: 20px;">Detail Stok<br> Pemasukan & Pengeluaran</h2>
	</div>	
</div>
<hr style="height: 2px;color: black;background-color: black;">
<div style="width: 56%;float: left;border: 0px solid black;">
<table>
	<tr>
		<td style="font-size: 15px;">ID Product</td>
		<td style="font-size: 15px;">:</td>
		<td style="font-size: 15px;"><?php echo $_GET['id_product']?></td>
	</tr>
	<tr>
		<td style="font-size: 15px;">Nama Product</td>
		<td style="font-size: 15px;">:</td>
		<td style="font-size: 15px;">
			<?php
			echo $_GET['nama_product'];
			?>
		</td>
	</tr>
	<tr>
		<td style="font-size: 15px;">STOK</td>
		<td style="font-size: 15px;">:</td>
		<td style="font-size: 15px; color: #4CAF50;"><?= $stok['total_stok'] ?></td>
	</tr>
</table>	
</div>
<div style="float: right;border: 0px solid black; margin-top: 12px;">
<table style="font-size: 14px;">
	<tr>
		<td style="font-size: 15px;"><?php echo $tanggal1 ?></td>
		<td style="font-size: 15px;">s/d</td>
		<td style="font-size: 15px;"><?php echo $tanggal2?></td>
	</tr>
</table>	
</div>

<center>
	<table style="width: 100%;font-size: 14px; padding-top: 10px;" cellspacing="0">
		<thead style="border: 1px solid black;">
			<th style="width: 2%;">NO.</th>
			<th style="width: 8%;">ID Order</th>
			<th style="width: 5%;">ID PRODUCT</th>
			<th style="width: 10%;">TANGGAL</th>
			<th style="width: 5%;">CABANG</th>
			<th style="width: 5%;">JUMLAH</th>
			<th style="width: 20%;">KETERANGAN</th>
			<th style="width: 5%;">Sisa</th>
		</thead>
	</table>
	<table style="width: 100%;font-size: 14px;" cellspacing="0">
		<tbody>
			<?php
			$no=1;
			$sisajml=0;
			$sisapen=0;
			$totkel=0;
			while ($modal=mysqli_fetch_array($result)) { 
				$hotline=$modal['hotline'];	
					if($hotline == 1){
						$style = "style='background-color:red'";
					}
					else{
						$style = "style='background-color:white'";
					} ?>
					<?php
					echo "<tr $style>"?>
					<td style="width: 2%;text-align: center;padding: 10px 10px 10px 10px;"><?= $no++; ?></td>
					<?php
					echo "<td style='width: 8%;text-align: left;padding: 5px 5px 5px 5px;'> $modal[id_order]</td>
					<td style='width: 5%;text-align: center;padding: 10px 5px 10px 5px;'> $modal[id_product]</td>
					<td style='width: 10%;text-align: center;padding: 5px 5px 5px 5px;'> $modal[tanggal_diterima]</td>"
					?>
					<?php
							if($modal['cabang'] == '1'){
								echo "<td style='width: 5%;text-align: center;padding: 5px 5px 5px 5px;'>Distributor</td>";
							}else{
								echo "<td style='width: 5%;text-align: center;padding: 5px 5px 5px 5px;'> $modal[cabang]</td>";
							}
					?>
					<?php
							echo "<td style='width: 5%;text-align: center;padding: 5px 5px 5px 5px;'>$modal[jumlah]</td>";
							if($modal['keterangan'] == '1'){
								echo "<td style='width: 20%;text-align: center;padding: 5px 5px 5px 5px;'>Transaksi $modal[nama_distributor]</td>";
							}else if($modal['keterangan'] == 'retur diterima'){
								echo "<td style='width: 20%;text-align: center;padding: 5px 5px 5px 5px;'>Transaksi Masuk Cabang</td>";
							}else if($modal['keterangan'] == 'diterima'){
								echo "<td style='width: 20%;text-align: center;padding: 5px 5px 5px 5px;'>Transaksi Keluar Cabang</td>";
							}else if($modal['keterangan'] == 'selesai'){
								echo "<td style='width: 20%;text-align: center;padding: 5px 5px 5px 5px;'>Transaksi Karyawan</td>";
							}
					?>
			<?php
					// $sisa = 0;
					// if($modal['keterangan'] == '1' && $modal['keterangan'] == 'retur diterima'){
					// 	$stok1 = $stok['stok_awal'] + $modal['jumlah'];		
					// 	for($sisa = $stok['stok_awal']; $modal['keterangan'] == '1' && $modal['keterangan'] == 'retur diterima'; $sisa + $end['jumlah']){
					// 		echo "<td style='width: 5%;text-align: center;padding: 5px 5px 5px 5px;'>$modal[hotline]</td>"
					// 	}			
					// }else if($modal['keterangan'] == 'diterima' && $modal['keterangan'] == 'selesai'){
					// 	$stok2 = $stok['stok_awal'] - $modal['jumlah'];
					// }
					echo "<td style='width: 5%;text-align: center;padding: 5px 5px 5px 5px;'>$modal[hotline]</td>
					</tr>";
				$total = 0; 
				if($modal['keterangan'] == '1' && $modal['keterangan'] == 'retur diterima'){
					$total = $modal['SUM(jumlah)'];
				} 
			} ?>
		</tbody>		
		<tfoot>
			<tr>
				<td style="width: 2%;text-align: center;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 8%;padding: 5px 5px 5px 5px;"><label style="font-size: 15px;">TOTAL PEMASUKAN</label></td>
				<td style="width: 5%;text-align: center;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 10%;text-align: right;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 5%;text-align: right;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 5%;text-align: right;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 20%;text-align: right;padding: 5px 5px 5px 5px;"></td>
				<?php
				?>
				<td style="width: 5%;text-align: right;padding: 5px 5px 5px 5px;"><label style="font-size: 20px;"><?php echo $total ?></label></td>
			</tr>
			<tr>
				<td style="width: 2%;text-align: center;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 8%;padding: 5px 5px 5px 5px;"><label style="font-size: 15px;">TOTAL PENGELUARAN</label></td>
				<td style="width: 5%;text-align: center;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 10%text-align: right;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 5%;;text-align: right;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 5%;;text-align: right;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 20%;text-align: right;padding: 5px 5px 5px 5px;"></td>
				<td style="width: 5%;text-align: right;padding: 5px 5px 5px 5px;"><label style="font-size: 20px;"><?= $total ?></label></td>
			</tr>
		</tfoot>
	</table>
	<?php
	$id_order=$_GET['id_order'];
	$query=mysqli_query($connection, "SELECT * FROM tbl_order_pusat WHERE id_order='$id_order' GROUP BY id_product");
	$data=mysqli_fetch_array($query);
	?>

</center>
Keterangan: <?php echo $data['keterangan'] ?>
<div style="width: 100%;margin-top: 10px;">
	<p>Demikian untuk digunakan sebagai bukti pengiriman dan penerimaan barang / product.<br>Komplain Maksimal 2x24 jam setelah barang diterima.<br>Barang yang sudah diterima tidak dapat dikembalikan.</p>
</div>
<div style="width: 25%;margin-top: 20px;text-align: center;float: left;">
	Disiapkan Oleh
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	<?= $_GET['nama_logistik'] ?>
</div>
<div style="width: 25%;margin-top: 20px;text-align: center;float: left;">
	Diperiksa Oleh
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	(...................................................)
</div>
<div style="width: 25%;margin-top: 20px;text-align: center;float: left;">
	Diketahui Oleh
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	(...................................................)
</div>
<div style="width: 25%;margin-top: 20px;text-align: center;float: left;">
	Diterima Oleh
	<br>
	<br>
	<br>
	<br>
	<br>
	<br>
	(...................................................)
</div>

<div style="width: 100%;margin-top: 20px;text-align: center;float: left;">
	<a href="order_product_pusat.php?cabang=asem" style="padding: 10px 10px 10px 10px;background-color: grey;color: black;text-decoration: none;" id="printbtn">Kembali</a>
	<button type="button" onclick="window.print()" style="padding: 10px 10px 10px 10px;background-color: green;color: white;" id="printbtn">Print</button>

</div>