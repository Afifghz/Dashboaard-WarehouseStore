<?php
  include 'conn.php';
  $sql = "SELECT sum(totalShip_cost) as tot from fact_shipping";
  $tot = mysqli_query($conn,$sql);
  $tot_amount = mysqli_fetch_row($tot);

  //echo $tot_amount[0];

  //query untuk ambil penjualan berdasarkan kategori, query sudah dimodifikasi
  //ditambahkan label variabel DATA. (teknik gak jelas :D)

$sql = "SELECT concat('name:',f.shipping_method_name) as name, concat('y:', sum(fp.totalShip_cost)*100/" . $tot_amount[0] .") as y, concat('drilldown:', f.shipping_method_name) as drilldown
          FROM shipping_method f
          JOIN fact_shipping fp ON (f.shipping_method_id = fp.shipping_method_id)
          GROUP BY name
          ORDER BY y DESC"   ;         
          //echo $sql;
  $all_kat = mysqli_query($conn,$sql);
  
  while($row = mysqli_fetch_all($all_kat)) {
      $data[] = $row;
  }
  

  $json_all_kat = json_encode($data);
  
  //CHART KEDUA (DRILL DOWN)

  //query untuk tahu SUM(Amount) semua kategori
  $sql = "SELECT f.shipping_method_name kategori, sum(fp.totalShip_cost) as tot_kat
          FROM fact_shipping fp
          JOIN shipping_method f ON (f.shipping_method_id = fp.shipping_method_id)
          GROUP BY kategori";
  $hasil_kat = mysqli_query($conn,$sql);

  while($row = mysqli_fetch_all($hasil_kat)){
      $tot_all_kat[] = $row;
  }

  //print_r($tot_all_kat);
  //function untuk nyari total_per_kat 

  //echo count($tot_per_kat[0]);
  //echo $tot_per_kat[0][0][1];
  
  function cari_tot_kat($kat_dicari, $tot_all_kat){
     $counter = 0;
     // echo $tot_all_kat[0];
     while( $counter < count($tot_all_kat[0]) ){
          if($kat_dicari == $tot_all_kat[0][$counter][0]){
              $tot_kat = $tot_all_kat[0][$counter][1];
              return $tot_kat;
          }
          $counter++;        
     }
  }

  //query untuk ambil penjualan di kategori berdasarkan bulan (clean)
  $sql = "SELECT f.shipping_method_name kategori, 
          t.bulan as bulan, 
          sum(fp.totalShip_cost) as pendapatan_kat
          FROM fact_shipping fp
          JOIN shipping_method f ON (f.shipping_method_id = fp.shipping_method_id)
          JOIN time t ON (t.time_id = fp.time_id)
          GROUP BY kategori, bulan";
  $det_kat = mysqli_query($conn,$sql);
  $i = 0;
  while($row = mysqli_fetch_all($det_kat)) {
      //echo $row;
      $data_det[] = $row;
      
  }

  //print_r($data_det);

  //PERSIAPAN DATA DRILL DOWN - TEKNIK CLEAN  
  $i = 0;

  //inisiasi string DATA
  $string_data = "";
  $string_data .= '{name:"' . $data_det[0][$i][0] . '", id:"' . $data_det[0][$i][0] . '", data: [';


  // echo cari_tot_kat("Action", $tot_all_kat);
  foreach($data_det[0] as $a){
      //echo cari_tot_kat($a[0], $tot_all_kat);

      if($i < count($data_det[0])-1){
          if($a[0] != $data_det[0][$i+1][0]){
              $string_data .= '["' . $a[1] . '", ' . 
                  $a[2]*100/cari_tot_kat($a[0], $tot_all_kat) . ']]},';
              $string_data .= '{name:"' . $a[0] . '", id:"' . $a[0]    . '", data: [';
          }
          else{
              $string_data .= '["' . $a[1] . '", ' . 
                  $a[2]*100/cari_tot_kat($a[0], $tot_all_kat) . '], ';
          }            
      }
      else{
          
              $string_data .= '["' . $a[1] . '", ' . 
                  $a[2]*100/cari_tot_kat($a[0], $tot_all_kat). ']]}';
             
      }
     
   
       $i = $i+1;
    
  }   
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Warehouse Store</title>

  <meta name="language" content="en-EN" />
  <meta name="author" content="Irfan Maulana" />
  <link rel="author" href="https://plus.google.com/u/0/+irfanmaulana-mazipan/posts" />
  <link rel="publisher" href="https://plus.google.com/u/0/+irfanmaulana-mazipan" />
  <meta name="keywords" content="bootstrap, bootstrap 4, bootstrap 4 template, bootstrap 4 admin, bootstrap 4 dashboard" />
  <meta name="description" content="Bootstrap 4 admin dashboard template by Irfan Maulana" />
  <meta property="og:title" content="Bootstrap 4 Admin Dashboard Template | Irfan Maulana" />
  <meta property="og:description" content="Bootstrap 4 admin dashboard template by Irfan Maulana" />
  <meta property="og:url" content="https://mazipan.github.io/bootstrap4-admin-dashboard-template/" />
  <meta property="og:site_name" content="Bootstrap 4 Admin Dashboard Template" />
  <meta property="og:type" content="website" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:site" content="@maz_ipan" />
  <meta name="twitter:creator" content="@maz_ipan" />
  <meta name="twitter:title" content="Bootstrap 4 Admin Dashboard Template | Irfan Maulana" />
  <meta name="twitter:description" content="Bootstrap 4 admin dashboard template by Irfan Maulana" />
  <meta name="twitter:domain" content="https://mazipan.github.io/bootstrap4-admin-dashboard-template/" />
  <link rel="home" href="https://mazipan.github.io/bootstrap4-admin-dashboard-template/">
  <link rel="icon" type="image/png" sizes="16x16" href="icon.png">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
    crossorigin="anonymous">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="dist/main.css">
  
  <!-- drilldown -->
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/data.js"></script>
  <script src="https://code.highcharts.com/modules/drilldown.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>
  <link rel="stylesheet" href="drilldown.css">

  <!-- Vanilla CSS -->
  <link rel="stylesheet" href="style.css">

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-25065548-2"></script>
  <script>window.dataLayer = window.dataLayer || []; function gtag() { dataLayer.push(arguments); } gtag('js', new Date()); gtag('config', 'UA-25065548-2');</script>

  <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
  <script>
  (adsbygoogle = window.adsbygoogle || []).push({
    google_ad_client: "ca-pub-5442972248172818",
    enable_page_level_ads: true
  });
  </script>
  
</head>

<body>

<header class="navbar navbar-expand sticky-top bg-primary navbar-dark flex-column flex-md-row bd-navbar">
    <a class="navbar-brand mr-0 mr-md-2" href="#">
      Warehouse Store
    </a>

    <ul class="navbar-nav flex-row ml-md-auto d-none d-md-flex">
      <li class="nav-item dropdown">
        <a class="nav-item nav-link dropdown-toggle mr-md-2 active" href="#" id="bd-versions" data-toggle="dropdown" aria-haspopup="true"
          aria-expanded="false">
          Hello, admin
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="bd-versions">
          <a class="dropdown-item" href="#">
            <i class="fa fa-cog pr-2"></i> Settings
          </a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="logout.php">
            <i class="fa fa-power-off pr-2"></i> Logout
          </a>
        </div>
      </li>

    </ul>

  </header>

  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-2 d-none d-md-block bg-light sidebar">
        <div class="sidebar-sticky">

          <h6 class="sidebar-heading">
            <span>Main Navigation</span>
          </h6>

          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="index.php">
                <i class="fa fa-product-hunt"></i>
                Warehouse
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="index2.php">
                <i class="fa fa-send"></i> Pengiriman
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="index3.php">
                <i class="fa fa-tags"></i> Penawaran
              </a>
            </li>
          </ul>
      </aside>
      <main class="col-md-10 ml-sm-auto col-lg-10 pt-3 px-4">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
          <h1 class="h2"><i class="fa fa-send"></i> Pengiriman</h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <button class="btn btn-sm btn-primary">Export</button>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-4 col-md-3 col-sm-12 pr-0 mb-3">
            <div class="card text-white bg-danger">
              <div class="card-header">Customer Order</div>
              <div class="card-body">
                <h3 class="card-title">
                  <span id="count">0</span>
                </h3>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-3 col-sm-12 pr-0 mb-3">
            <div class="card text-white bg-success">
              <div class="card-header">Address Reach</div>
              <div class="card-body">
                <h3 class="card-title">
                  <span id="count1">0</span>
                </h3>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-3 col-sm-12 pr-0 mb-3">
            <div class="card text-white bg-primary">
              <div class="card-header">Total Biaya Pengiriman</div>
              <div class="card-body">
                <h3 class="card-title">
                  <span id="count2">0</span>
                </h3>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 pr-0 mb-3">
            <div class="card text-white bg-secondary">
              <div class="card-header">Total Freight</div>
              <div class="card-body">
                <h3 class="card-title">
                  <span id="count3">0</span>
                </h3>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 pr-0 mb-3">
            <div class="card text-white bg-info">
              <div class="card-header">Total Taxamt</div>
              <div class="card-body">
                <h3 class="card-title">
                  <span id="count4">0</span>
                </h3>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 pr-0 mb-3">
            <div class="card text-white bg-warning">
              <div class="card-header">Total Ship Base</div>
              <div class="card-body">
                <h3 class="card-title">
                  <span id="count5">0</span>
                </h3>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-3 col-sm-12 pr-0 mb-3">
            <div class="card text-white bg-warning">
              <div class="card-header">Total Ship Rate</div>
              <div class="card-body">
                <h3 class="card-title">
                  <span id="count6">0</span>
                </h3>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6 col-md-6 col-sm-12 pr-0 mb-3">
            <div class="card-collapsible card">
              <div class="card-header">
                Data Ekspedisi
              </div>
              <div class="card-body d-flex justify-content-around">
                  <canvas class="chart w-100" id="pieChart"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-12 pr-0 mb-3">
            <div class="card-collapsible card">
              <div class="card-header">
                Data Ekpekdisi per Tahun<i class="fa fa-caret-down caret"></i>
              </div>
              <div class="card-body d-flex justify-content-around">
                  <canvas class="chart w-100" id="barChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-24 pr-0 mb-3">
            <div class="card-collapsible card">
              <div class="card-header">
                List Ekspekdisi <i class="fa fa-caret-down caret"></i>
              </div>
              <div class="card-body">
                <table class="table">
                  <thead class="thead bg-primary text-white">
                    <tr>
                      <th scope="col">No</th>
                      <th scope="col">Name</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      include 'conn.php';
                      $query = "SELECT shipping_method_id, shipping_method_name FROM shipping_method";
                      $result = mysqli_query($conn, $query);
                    ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                      <tr>
                        <td><?php echo $row['shipping_method_id']; ?></td>
                        <td><?php echo $row['shipping_method_name']; ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-6 col-md-6 col-sm-12 pr-0 mb-3">
            <div class="card-collapsible card">
              <div class="card-header">
                Grafik Pengriman berdasarkan Produk dan Customer
              </div>
              <div class="card-body d-flex justify-content-around">
                <canvas id="lineChart"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-6 col-sm-12 pr-0 mb-3">
            <div class="card-collapsible card">
              <div class="card-header">
                Grafik Pendapatan Pengiriman 
              </div>
              <div class="card-body d-flex justify-content-around">
                <canvas id="line1Chart"></canvas>
              </div>
            </div>
          </div>
          <div class="col-lg-12 col-md-6 col-sm-24 pr-0 mb-3">
            <div class="card-collapsible card">
              <div class="card-header">
                Persentase Pengiriman
              </div>
              <div class="card-body d-flex justify-content-around">
                <figure class="highcharts-figure">
                <div id="container"></div>
                <p class="highcharts-description">
                </p>
                </figure>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
    crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
    integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
    crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
    integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
    crossorigin="anonymous"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.js"></script>
  <script src="js/main.js"></script>

  <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
  <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-5442972248172818" data-ad-slot="1487770485" data-ad-format="auto"></ins>
  <script>
  (adsbygoogle = window.adsbygoogle || []).push({});
  </script>
  
  <script type="application/ld+json">{"@context":"http://schema.org","@type":"WebSite","url":"https://www.mazipan.github.io/","name":"Irfan Maulana | Front End Developer","author":"Irfan Maulana","image":"http://mazipan.github.io/images/irfan-maulana.jpg","description":"Irfan Maulana is Front End Developer from Indonesia - Man that craft some code to build a beauty and readable code, experienced in web and desktop technology.","sameAs":["https://www.facebook.com/mazipanneh","https://instagram.com/maz_ipan","https://twitter.com/Maz_Ipan","https://id.linkedin.com/in/irfanmaulanamazipan","https://www.slideshare.net/IrfanMaulana21","https://github.com/mazipan"]}</script>
  <script type="application/ld+json">{"@context":"http://schema.org","@type":"Person","email":"mailto:mazipanneh@gmail.com","image":"http://mazipan.github.io/images/irfan-maulana.jpg","jobTitle":"Software Engineer","name":"Irfan Maulana","url":"https://www.mazipan.github.io/","sameAs":["https://www.facebook.com/mazipanneh","https://instagram.com/maz_ipan","https://twitter.com/Maz_Ipan","https://id.linkedin.com/in/irfanmaulanamazipan","https://www.slideshare.net/IrfanMaulana21","https://github.com/mazipan"]}</script>
  <script type="application/ld+json">{"@context":"http://schema.org","@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"item":{"@id":"http://mazipan.github.io/","name":"Home","image":"http://mazipan.github.io/images/irfan-maulana.jpg"}},{"@type":"ListItem","position":2,"item":{"@id":"http://mazipan.github.io/demo/","name":"Demo","image":"http://mazipan.github.io/images/irfan-maulana.jpg"}},{"@type":"ListItem","position":3,"item":{"@id":"http://mazipan.github.io/bootstrap4-admin-dashboard-template","name":"bootstrap4-admin-dashboard-template","image":"http://mazipan.github.io/images/irfan-maulana.jpg"}}]}</script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Script Customer Order-->
  <script>
    $(document).ready(function() {
      var countElement = $('#count');
      <?php
      include 'conn.php';
      $sql = "SELECT COUNT(DISTINCT customer_id) AS total FROM fact_shipping";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_assoc($result);
      $count = $row['total'];
      ?>
      var targetCount = <?php echo $count; ?>; // Menggunakan nilai dari PHP
      var duration = 3000; // Durasi animasi dalam milidetik

      $({ count: 0 }).animate({ count: targetCount }, {
        duration: duration,
        easing: 'linear',
        step: function() {
          countElement.text(Math.floor(this.count).toLocaleString()); // Mengubah format angka ribuan
        },
        complete: function() {
          countElement.text(targetCount.toLocaleString()); // Mengubah format angka ribuan
        }
      });
    });
</script>

  <!-- Script Persebaran Alamat-->
  <script>
    $(document).ready(function() {
      var countElement = $('#count1');
      <?php
      include 'conn.php';
      $sql = "SELECT COUNT(DISTINCT a.city) AS total FROM whstore.address a JOIN fact_shipping f ON a.address_id = f.address_id";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_assoc($result);
      $count = $row['total'];
      ?>
      var targetCount = <?php echo $count; ?>; // Menggunakan nilai dari PHP
      var duration = 3000; // Durasi animasi dalam milidetik

      $({ count: 0 }).animate({ count: targetCount }, {
        duration: duration,
        easing: 'linear',
        step: function() {
          countElement.text(Math.floor(this.count));
        },
        complete: function() {
          countElement.text(targetCount);
        },
        complete: function() {
          countElement.text(targetCount.toLocaleString()); // Mengubah format angka ribuan
        }
      });
    });
  </script>

  <!-- Script Total -->
  <script>
    $(document).ready(function() {
      var countElement = $('#count2');
      <?php
      include 'conn.php';
      $sql = "SELECT SUM(f.totalShip_cost) AS total FROM whstore.fact_shipping f";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_assoc($result);
      $count = $row['total'];
      ?>
      var targetCount = <?php echo $count; ?>; // Menggunakan nilai dari PHP
      var duration = 3000; // Durasi animasi dalam milidetik

      $({ count: 0 }).animate({ count: targetCount }, {
        duration: duration,
        easing: 'linear',
        step: function() {
          countElement.text('$' + Math.floor(this.count));
        },
        complete: function() {
          countElement.text('$' + targetCount);
        },
        complete: function() {
          countElement.text('$' + targetCount.toLocaleString()); // Mengubah format angka ribuan
        }
      });
    });
</script>

<!-- Script Freight -->
<script>
    $(document).ready(function() {
      var countElement = $('#count3');
      <?php
      include 'conn.php';
      $sql = "SELECT SUM(f.freight) AS total FROM whstore.fact_shipping f";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_assoc($result);
      $count = $row['total'];
      ?>
      var targetCount = <?php echo $count; ?>; // Menggunakan nilai dari PHP
      var duration = 3000; // Durasi animasi dalam milidetik

      $({ count: 0 }).animate({ count: targetCount }, {
        duration: duration,
        easing: 'linear',
        step: function() {
          countElement.text('$' + Math.floor(this.count));
        },
        complete: function() {
          countElement.text('$' + targetCount);
        },
        complete: function() {
          countElement.text('$' + targetCount.toLocaleString()); // Mengubah format angka ribuan
        }
      });
    });
</script>

<!-- Script TaxAmt -->
<script>
    $(document).ready(function() {
      var countElement = $('#count4');
      <?php
      include 'conn.php';
      $sql = "SELECT SUM(f.taxAmt) AS total FROM whstore.fact_shipping f";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_assoc($result);
      $count = $row['total'];
      ?>
      var targetCount = <?php echo $count; ?>; // Menggunakan nilai dari PHP
      var duration = 3000; // Durasi animasi dalam milidetik

      $({ count: 0 }).animate({ count: targetCount }, {
        duration: duration,
        easing: 'linear',
        step: function() {
          countElement.text('$' + Math.floor(this.count));
        },
        complete: function() {
          countElement.text('$' + targetCount);
        },
        complete: function() {
          countElement.text('$' + targetCount.toLocaleString()); // Mengubah format angka ribuan
        }
      });
    });
</script>

<!-- Script ShipBase -->
<script>
    $(document).ready(function() {
      var countElement = $('#count5');
      <?php
      include 'conn.php';
      $sql = "SELECT SUM(f.ship_base) AS total FROM whstore.fact_shipping f";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_assoc($result);
      $count = $row['total'];
      ?>
      var targetCount = <?php echo $count; ?>; // Menggunakan nilai dari PHP
      var duration = 3000; // Durasi animasi dalam milidetik

      $({ count: 0 }).animate({ count: targetCount }, {
        duration: duration,
        easing: 'linear',
        step: function() {
          countElement.text('$' + Math.floor(this.count));
        },
        complete: function() {
          countElement.text('$' + targetCount);
        },
        complete: function() {
          countElement.text('$' + targetCount.toLocaleString()); // Mengubah format angka ribuan
        }
      });
    });
</script>

<!-- Script ShipRatee -->
<script>
    $(document).ready(function() {
      var countElement = $('#count6');
      <?php
      include 'conn.php';
      $sql = "SELECT SUM(f.ship_rate) AS total FROM whstore.fact_shipping f";
      $result = mysqli_query($conn, $sql);
      $row = mysqli_fetch_assoc($result);
      $count = $row['total'];
      ?>
      var targetCount = <?php echo $count; ?>; // Menggunakan nilai dari PHP
      var duration = 3000; // Durasi animasi dalam milidetik

      $({ count: 0 }).animate({ count: targetCount }, {
        duration: duration,
        easing: 'linear',
        step: function() {
          countElement.text('$' + Math.floor(this.count));
        },
        complete: function() {
          countElement.text('$' + targetCount);
        },
        complete: function() {
          countElement.text('$' + targetCount.toLocaleString()); // Mengubah format angka ribuan
        }
      });
    });
</script>

  <!-- Script Chart -->
  <script>
    $(document).ready(function() {
    initPieChart();
    initBarChart();
    initlineChart();
    initline1Chart();
    });

    function initlineChart() {
      // Line Chart
      var ctxL = document.getElementById("lineChart").getContext('2d');
      var labels = ["2001", "2002", "2003", "2004"];
      
      // Query data for Produk Diskon
      var produkData = [
        <?php
          $tahun = 2001;
          while ($tahun <= 2004) {
            $query = "SELECT COUNT(DISTINCT f.product_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id WHERE t.tahun = '$tahun'";
            $jumlah = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($jumlah);
            echo $row[0] . ",";
            $tahun++;
          }
        ?>
      ];

      // Query data for Toko Diskon
      var tokoData = [
        <?php
          $tahun = 2001;
          while ($tahun <= 2004) {
            $query = "SELECT COUNT(DISTINCT f.customer_id) FROM fact_shipping f JOIN whstore.time t ON f.time_id = t.time_id WHERE t.tahun = '$tahun'";
            $jumlah = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($jumlah);
            echo $row[0] . ",";
            $tahun++;
          }
        ?>
      ];

      var myLineChart = new Chart(ctxL, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: "Produk",
            data: produkData,
            backgroundColor: 'rgba(105, 0, 132, .2)',
            borderColor: 'rgba(200, 99, 132, .7)',
            borderWidth: 5
          },
          {
            label: "Toko",
            data: tokoData,
            backgroundColor: 'rgba(0, 137, 132, .2)',
            borderColor: 'rgba(0, 10, 130, .7)',
            borderWidth: 5
          }]
        },
        options: {
          responsive: true,
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
                max: 15000, // Atur nilai maksimum sesuai kebutuhan Anda
              }
            }]
          }
        }
      });
    }

    function initline1Chart() {
  // Line Chart
  var ctxL = document.getElementById("line1Chart").getContext('2d');
  var labels = ["2001", "2002", "2003", "2004"];

  // Query data for Total Ship Cost
  var shipCostData = [
    <?php
      $tahun = 2001;
      while ($tahun <= 2004) {
        $query = "SELECT SUM(f.totalShip_cost) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id WHERE t.tahun = '$tahun'";
        $jumlah = mysqli_query($conn, $query);
        $row = mysqli_fetch_row($jumlah);
        echo $row[0] . ",";
        $tahun++;
      }
    ?>
  ];

  var myLineChart = new Chart(ctxL, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: "Total Ship Cost",
        data: shipCostData,
        backgroundColor: 'rgba(105, 0, 132, .2)',
        borderColor: 'rgba(200, 99, 132, .7)',
        borderWidth: 5
      }]
    },
    options: {
      responsive: true,
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true,
            max: 1000000000, // Atur nilai maksimum sesuai kebutuhan Anda
          }
        }]
      }
    }
  });
}


    function initPieChart() {
  //-------------
  //- PIE CHART -
  //-------------
  var pieOptions = {
    responsive: true,
    segmentShowStroke: true,
    segmentStrokeColor: '#fff',
    segmentStrokeWidth: 1,
    animationSteps: 100,
    animationEasing: 'easeOutBounce',
    animateRotate: true,
    animateScale: true,
    maintainAspectRatio: true,
    legend: {
      display: true,
      position: 'right',
      labels: {
        boxWidth: 15,
        defaultFontColor: '#343a40',
        defaultFontSize: 11,
      }
    },
    tooltips: {
      callbacks: {
        label: function(tooltipItem, data) {
          var dataset = data.datasets[tooltipItem.datasetIndex];
          var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
            return previousValue + currentValue;
          });
          var currentValue = dataset.data[tooltipItem.index];
          var percentage = Math.floor(((currentValue / total) * 100) + 0.5);
          var label = data.labels[tooltipItem.index];
          var count = dataset.data[tooltipItem.index];
          return label + ': ' + count + ' (' + percentage + '%)';
        }
      }
    }
  };

  <?php 
    include 'conn.php';
  ?>

  var ctx = document.getElementById("pieChart");
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      datasets: [{
        data: [
          <?php
            $query =  "SELECT COUNT(customer_id) FROM fact_shipping WHERE shipping_method_id = '1'";
            $jumlah_XRQ = mysqli_query($conn, $query);
            $row_XRQ = mysqli_fetch_row($jumlah_XRQ);
            echo $row_XRQ[0];
          ?>,
          <?php
            $query =  "SELECT COUNT(customer_id) FROM fact_shipping WHERE shipping_method_id = '2'";
            $jumlah_ZY = mysqli_query($conn, $query);
            $row_ZY = mysqli_fetch_row($jumlah_ZY);
            echo $row_ZY[0];
          ?>,
          <?php
            $query =  "SELECT COUNT(customer_id) FROM fact_shipping WHERE shipping_method_id = '3'";
            $jumlah_OS = mysqli_query($conn, $query);
            $row_OS = mysqli_fetch_row($jumlah_OS);
            echo $row_OS[0];
          ?>,
          <?php
            $query =  "SELECT COUNT(customer_id) FROM fact_shipping WHERE shipping_method_id = '4'";
            $jumlah_ON = mysqli_query($conn, $query);
            $row_ON = mysqli_fetch_row($jumlah_ON);
            echo $row_ON[0];
          ?>,
          <?php
            $query =  "SELECT COUNT(customer_id) FROM fact_shipping WHERE shipping_method_id = '5'";
            $jumlah_CT = mysqli_query($conn, $query);
            $row_CT = mysqli_fetch_row($jumlah_CT);
            echo $row_CT[0];
          ?>
        ],
        backgroundColor: [
          '#f56954',
          '#00a65a',
          '#f39c12',
          '#00c0ef',
          '#3c8dbc',
        ],
      }],
      labels: [
        'XRQ - TRUCK GROUND',
        'ZY - EXPRESS',
        'OVERSEAS - DELUXE',
        'OVERNIGHT J-FAST',
        'CARGO TRANSPORT 5',
      ]
    },
    options: pieOptions
  });
}


    function initBarChart() {
      //-------------
      //- BAR CHART -
      //-------------
      var areaChartData = {
        labels: ['2001', '2002', '2003', '2004'],
        datasets: [
          {
            label: 'XRQ - TRUCK GROUND',
            backgroundColor: '#f56954',
            data: [
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '1' && tahun='2001'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '1' && tahun='2002'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '1' && tahun='2003'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '1' && tahun='2004'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>
            ]
          },
          {
            label: 'ZY - EXPRESS',
            backgroundColor: '#00a65a',
            data: [
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '2' && tahun='2001'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '2' && tahun='2002'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '2' && tahun='2003'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '2' && tahun='2004'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>
            ]
          },
          {
            label: 'OVERSEAS - DELUXE',
            backgroundColor: '#f39c12',
            data: [
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '3' && tahun='2001'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '3' && tahun='2002'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '3' && tahun='2003'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '3' && tahun='2004'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>
            ]
          },
          {
            label: 'OVERNIGHT J-FAST',
            backgroundColor: '#00c0ef',
            data: [
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '4' && tahun='2001'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '4' && tahun='2002'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '4' && tahun='2003'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '4' && tahun='2004'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>
            ]
          },
          // Tambahkan dataset baru di sini
          {
            label: 'CARGO TRANSPORT 5',
            backgroundColor: '#3c8dbc',
            data: [
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '5' && tahun='2001'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '5' && tahun='2002'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '5' && tahun='2003'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>,
              <?php
                $query =  "SELECT COUNT(f.customer_id) FROM fact_shipping f JOIN whstore.time t ON t.time_id = f.time_id  WHERE shipping_method_id = '5' && tahun='2004'";
                $jumlah_CT = mysqli_query($conn, $query);
                $row_CT = mysqli_fetch_row($jumlah_CT);
                echo $row_CT[0];
              ?>
            ]
          }
        ]
      };

      var barChartOptions = {
        // Pengaturan lainnya
      };

      var ctxBar = document.getElementById("barChart");
      new Chart(ctxBar, {
        type: 'bar',
        data: areaChartData,
        options: barChartOptions
      });
    }


  </script>

  <!-- drill down -->
  <script type="text/javascript">
// Create the chart
Highcharts.chart('container', {
    chart: {
        type: 'pie'
    },
    title: {
        text: 'Persentase Penawaran'
    },
    subtitle: {
        text: 'Klik di potongan kue untuk melihat detail produk ditawar berdasarkan bulan'
    },

    accessibility: {
        announceNewData: {
            enabled: true
        },
        point: {
            valueSuffix: '%'
        }
    },

    plotOptions: {
        series: {
            dataLabels: {
                enabled: true,
                format: '{point.name}: {point.y:.1f}%'
            }
        }
    },

    tooltip: {
        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
    },

    series: [
        {
            name: "Pendapatan By Kategori",
            colorByPoint: true,
            data: 
                <?php 
                    //TEKNIK GAK JELAS :D

                    $datanya =  $json_all_kat; 
                    $data1 = str_replace('["','{"',$datanya) ;   
                    $data2 = str_replace('"]','"}',$data1) ;  
                    $data3 = str_replace('[[','[',$data2);
                    $data4 = str_replace(']]',']',$data3);
                    $data5 = str_replace(':','" : "',$data4);
                    $data6 = str_replace('"name"','name',$data5);
                    $data7 = str_replace('"drilldown"','drilldown',$data6);
                    $data8 = str_replace('"y"','y',$data7);
                    $data9 = str_replace('",',',',$data8);
                    $data10 = str_replace(',y','",y',$data9);
                    $data11 = str_replace(',y : "',',y : ',$data10);
                    echo $data11;
                ?>
            
        }
    ],
    drilldown: {
        series: [
            
                <?php 
                    //TEKNIK CLEAN
                    echo $string_data;

                ?>

                
            
        ]
    }
});
</script>

</body>

</html>
