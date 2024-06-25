<?php 
    require 'config.php';
    include $view;
    $lihat = new view($config);

    $bulan_tes = array(
        '01' => "Januari",
        '02' => "Februari",
        '03' => "Maret",
        '04' => "April",
        '05' => "Mei",
        '06' => "Juni",
        '07' => "Juli",
        '08' => "Agustus",
        '09' => "September",
        '10' => "Oktober",
        '11' => "November",
        '12' => "Desember"
    );

    // Proses pencarian nama barang
    if(isset($_GET['cari_nama'])) {
        $nama_barang = $_GET['cari_nama'];
        $hasil = $lihat->nama_jual($nama_barang);
    } else {
        // Jika tidak ada pencarian, tampilkan laporan bulanan atau harian
        if(!empty($_GET['cari'])){
            $periode = $_GET['bln'].'-'.$_GET['thn'];
            $hasil = $lihat->periode_jual($periode);
        } elseif(!empty($_GET['hari'])) {
            $hari = $_GET['tgl'];
            $hasil = $lihat->hari_jual($hari);
        } else {
            $hasil = $lihat->jual();
        }
    }

    // Fungsi untuk membuat file Excel dari data yang ada
    if(isset($_GET['export'])) {
        require_once 'PHPExcel.php';

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->setTitle('Data Penjualan');

        $rowCount = 1;
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A1', 'No')
                    ->setCellValue('B1', 'ID Barang')
                    ->setCellValue('C1', 'Nama Barang')
                    ->setCellValue('D1', 'Jumlah')
                    ->setCellValue('E1', 'Modal')
                    ->setCellValue('F1', 'Total');

        foreach ($hasil as $key => $data) {
            $rowCount++;
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.$rowCount, $key+1)
                        ->setCellValue('B'.$rowCount, $data['id_barang'])
                        ->setCellValue('C'.$rowCount, $data['nama_barang'])
                        ->setCellValue('D'.$rowCount, $data['jumlah'])
                        ->setCellValue('E'.$rowCount, 'Rp.'.number_format($data['harga_beli'] * $data['jumlah']).',-')
                        ->setCellValue('F'.$rowCount, 'Rp.'.number_format($data['total']).',-');
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="data-laporan-'.date('Y-m-d').'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        .ttd {
            width: 100%;
            margin-top: 10%;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        .ttd div {
            width: 30%;
        }
        .ttd p {
            margin: 0;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="btn btn-primary" onclick="window.print()">Cetak</button>
    
    
    

    <!-- Tabel laporan penjualan -->
    <div class="modal-view">
        <h3 style="text-align:center;"> 
            <?php if(!empty($_GET['cari'])){ ?>
                LAPORAN PENJUALAN BULANAN <br><br> Bulan : <?= $bulan_tes[$_GET['bln']];?> <?= $_GET['thn'];?>
            <?php }elseif(!empty($_GET['hari'])){?>
                LAPORAN PENJUALAN HARIAN <br><br> <?= $_GET['tgl'];?>
            <?php } elseif(isset($_GET['cari_nama'])) {?>
                LAPORAN PENJUALAN BERDASARKAN NAMA BARANG <br><br> Nama Barang: <?= $_GET['cari_nama'];?>
            <?php } else {?>
                Data Laporan Penjualan <?= $bulan_tes[date('m')];?> <?= date('Y');?>
            <?php }?>
        </h3>
        <table border="1" width="100%" cellpadding="1" cellspacing="0">
            <thead>
                <tr bgcolor="yellow">
                    <th style="width:10%;"> No</th>
                    <th> ID Barang</th>
                    <th> Nama Barang</th>
                    <th style="width:10%;"> Jumlah</th>
                    <th style="width:10%;"> Modal</th>
                    <th style="width:10%;"> Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $no = 1; 
                    $bayar = 0;
                    $jumlah = 0;
                    $modal = 0;

                    foreach ($hasil as $isi) { 
                        $bayar += $isi['total'];
                        $modal += $isi['harga_beli'] * $isi['jumlah'];
                        $jumlah += $isi['jumlah'];
                ?>
                <tr>
                    <td><?php echo $no;?></td>
                    <td><?php echo $isi['id_barang'];?></td>
                    <td><?php echo $isi['nama_barang'];?></td>
                    <td><?php echo $isi['jumlah'];?> </td>
                    <td>Rp.<?php echo number_format($isi['harga_beli']* $isi['jumlah']);?>,-</td>
                    <td>Rp.<?php echo number_format($isi['total']);?>,-</td>
                </tr>
                <?php $no++; }?> 
                <tr>
                    <td><b>Total Terjual</b></td>
                    <td></td>
                    <td></td>
                    <td><b><?php echo $jumlah;?></b></td>
                    <td><b>Rp.<?php echo number_format($modal);?>,-</b></td>
                    <td><b>Rp.<?php echo number_format($bayar);?>,-</b></td>
                </tr>
                <tr>
                    <td colspan="5"><b>Keuntungan</b></td>
                    <td><b>Rp.<?php echo number_format($bayar - $modal);?>,-</b></td>
                </tr>
            </tbody>
        </table>
        <div class="ttd">
            <div>
                <p>Pimpinan</p>
                <br><br><br>
                <p>(__________________)</p>
            </div>
            <div>
                <p>Bandar Lampung, <?php echo date("j F Y");?></p>
                <p>Kasir</p>
                <br><br><br>
                <p>(__________________)</p>
            </div>
        </div>
    </div>
</body>
</html>
