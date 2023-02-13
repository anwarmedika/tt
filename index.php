<?php
$host = "192.168.11.5";
$user = "postgres";
$password = "@RSAM33";
$port = "5432";
$dbname = "arafah";
$koneksi = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password") or die("Koneksi gagal");

$qry = pg_query(
    "SELECT room.room_code,room.apclass_code,
	room.room_name,room.capacity,
	SUM (case WHEN bed.bed_status_id = 114 THEN 1 ELSE 0 END) AS terpakai,
	room.capacity-(SUM (case WHEN bed.bed_status_id = 114 THEN 1 ELSE 0 END)) as kosong
	from yanmed.ms_bed bed
	left join admin.ms_room room on room.room_id = bed.room_id
	left join admin.ms_unit msu on room.unit_id = msu.unit_id
	where
	bed.is_active=true
	and
	room.is_active  is true 
	and
	msu.unit_active  is true  
	and
	bed.is_extra = false
	group by room.room_code,room.apclass_code,room.room_name,room.capacity
	"
);

$tersedia_kl1 = 0;
$tersedia_kl2 = 0;
$tersedia_kl3 = 0;
$tersedia_icu = 0;
$tersedia_iso = 0;
$total_kl1 = 0;
$total_kl2 = 0;
$total_kl3 = 0;
$total_icu = 0;
$total_iso = 0;


$resultSet = array();
while ($result = pg_fetch_array($qry)) {
    $resultSet[] = $result;
}

$lotus_jadi_isolasi = false;
foreach ($resultSet as $key => $d) {
    if (($d['room_code'] == 'L1i' || $d['room_code'] == 'L2i' || $d['room_code'] == 'L3i' || $d['room_code'] == 'L4i') && $d['terpakai'] >= 1) {
        $lotus_jadi_isolasi = true;
    }
}

foreach ($resultSet as $key => $d) {


    if ($lotus_jadi_isolasi) {
        if ($d['room_code'] == 'L1' || $d['room_code'] == 'L2' || $d['room_code'] == 'L3' || $d['room_code'] == 'L4') {
            $d['kosong'] = 0;
        }
    } else {
        if ($d['room_code'] == 'L1i' || $d['room_code'] == 'L2i' || $d['room_code'] == 'L3i' || $d['room_code'] == 'L4i') {
            $d['capacity'] = 0;
            $d['kosong'] = 0;
        }
    }

    if ($d['room_code'] == 'K12' && $d['kosong'] >= 1) {
		$d['kosong'] = $d['kosong'] - 1;
	}
	
    
    // if ($d['room_code'] == 'A7' && $d['kosong'] >= 1) {
    //     $d['kosong'] = $d['kosong'] - 1;
    // }

    // if ($d['room_code'] == 'K14') {
    //     $d['kosong'] = 0;
    //     $d['capacity'] = 0;
    // }

    if ($d['apclass_code'] == 'KL1') {
        $tersedia_kl1 = $tersedia_kl1 + (int)$d['kosong'];
        $total_kl1 = $total_kl1 + (int)$d['capacity'];
    } else if ($d['apclass_code'] == 'KL2') {
        $tersedia_kl2 = $tersedia_kl2 + (int)$d['kosong'];
        $total_kl2 = $total_kl2 + (int)$d['capacity'];
    } else if ($d['apclass_code'] == 'KL3') {
        $tersedia_kl3 = $tersedia_kl3 + (int)$d['kosong'];
        $total_kl3 = $total_kl3 + (int)$d['capacity'];
    } else if ($d['apclass_code'] == 'ICU') {
        $tersedia_icu = $tersedia_icu + (int)$d['kosong'];
        $total_icu = $total_icu + (int)$d['capacity'];
    } else if ($d['apclass_code'] == 'ISO') {
        $tersedia_iso = $tersedia_iso + (int)$d['kosong'];
        $total_iso = $total_iso + (int)$d['capacity'];
    }
}


?>

<!DOCTYPE html>

<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="refresh" content="600">

    <title>Ketersediaan Tempat Tidur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="bootstrap-5.1.3/css/bootstrap.css">
    <style>
        .card-header {
            background-color: darkgreen;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 40px;
        }

        p {
            font-weight: bold;
            text-align: center;
            font-size: 80px;
        }
    </style>
</head>


<body style="background-color: greenyellow;">
    <div class="container-fluid">
        <div class="row mt-2">
            <div class="col">
                <p style="text-align: center;font-weight: bold;color: black;font-size: 65px;">Ketersediaan Tempat Tidur</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-1"></div>
                    <div class="col-2 mb-3">
                        <div class="card shadow rounded">
                            <div class="card-header">KELAS 1</div>
                            <div class="card-body">
                                <p style="text-align: center;font-size: 25px;">Tersedia : </p>
                                <p>
                                    <?= $tersedia_kl1; ?>
                                </p>
                                <p style="font-size: 20px;text-align: right;"> Total Tempat Tidur : <?= $total_kl1; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 mb-3">
                        <div class="card shadow rounded">
                            <div class="card-header">KELAS 2</div>
                            <div class="card-body">
                                <p style="text-align: center;font-size: 25px;">Tersedia : </p>
                                <p><?= $tersedia_kl2; ?></p>
                                <p style="font-size: 20px;text-align: right;"> Total Tempat Tidur : <?= $total_kl2; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 mb-3">
                        <div class="card shadow rounded">
                            <div class="card-header">KELAS 3</div>
                            <div class="card-body">
                                <p style="text-align: center;font-size: 25px;">Tersedia : </p>
                                <p><?= $tersedia_kl3; ?></p>
                                <p style="font-size: 20px;text-align: right;"> Total Tempat Tidur : <?= $total_kl3; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 mb-3">
                        <div class="card shadow rounded">
                            <div class="card-header" style="background-color: darkred;">ICU</div>
                            <div class="card-body">
                                <p style="text-align: center;font-size: 25px;">Tersedia : </p>
                                <p><?= $tersedia_icu; ?></p>
                                <p style="font-size: 20px;text-align: right;"> Total Tempat Tidur : <?= $total_icu; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 mb-3">
                        <div class="card shadow rounded">
                            <div class="card-header" style="background-color: navy;">ISOLASI</div>
                            <div class="card-body">
                                <!-- <p style="text-align: center;font-size: 25px;"> . </p>
                        <p>! RENOVASI !</p>
                        <p style="font-size: 20px;text-align: right;">.</p> -->

                                <p style="text-align: center;font-size: 25px;">Tersedia : </p>
                                <p><?= $tersedia_iso; ?></p>
                                <p style="font-size: 20px;text-align: right;"> Total Tempat Tidur : <?= $total_iso; ?></p>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <video class="w-100" controls autoplay loop style="max-height: 600px;">
                        <source src="mjkn.mp4" type="video/mp4">
                    </video>
                </div>
            </div>

        </div>
    </div>
    <script src="bootstrap-5.1.3/js/jquery.custom.js"></script>

</body>

</html>