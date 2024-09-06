<?php
session_start();
include '../config/connect.php';

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['username']);

// Initialize wallet balance
$walletBalance = isset($_SESSION['walletbalance']) ? $_SESSION['walletbalance'] : 0; 
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LnwItem | Home</title>
    <link rel="stylesheet" href="../css/home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
       
        footer {
            background-color: white; 
            color: #8BA4DD; 
            padding: 10px;
            position: static;
            bottom: 0;
            width: 100%;
        }

        .contact-info span {
            font-weight: bold;
            color: #8BA4DD; 
            ; 
        }

        footer p {
            margin: 0; 
        }
    </style>
</head>
<body>
    <!-- start navbar -->
    <header>
    <img src="../img/lnwlogo.png" alt="Logo" width="100" >

        <nav>
            <a href="home.php">หน้าแรก</a>
            <a href="market.php">ตลาด</a>
            <a href="#">สนันสนุน</a>
        </nav>
        <div class="user-info">
    <img src="../img/icon.png" alt="User Icon" width="30">
    <?php if ($isLoggedIn): ?>
        <span>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <span>ยอดเงินของคุณ: ฿<?php echo number_format($walletBalance, 2); ?></span>
        <a href="../service/logout.php" class="btn btn-danger" style="margin-left: 10px;">Logout</a>
    <?php else: ?>
        <a href="./authentication/login.php" class="btn btn-success" style="margin-left: 10px;">Login</a>
    <?php endif; ?>
</div>
    </header>
<!-- end of navbar -->

    <!-- photo slide -->
    <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="../img/cs2.jpg" class="d-block w-100 " alt="">
          </div>
          <div class="carousel-item">
            <img src="../img/apex.jpg" class="d-block w-100" alt="">
          </div>
          <div class="carousel-item">
            <img src="../img/dota.jpg" class="d-block w-100" alt="">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
      <!-- end of photo slide -->

      <!-- hot item -->
      <div class="hot-items text-center">
        <h2 class="d-inline-block p-3 bg-white border rounded">กำลังมาแรง <span>🔥</span></h2>
        <div class="row justify-content-center">
            <div class="col-md-3">
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center"></div>
                    <img src="../img/Karambit_Lore.png" class="card-img-top" alt="" style="width: 295px; max-width: 100%; height: 220px;">
                    <div class="card-body">
                        <h5 class="card-title">Karambit Dragon Lore (FT)</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="ViewProduct.html" class="btn btn-warning">ดูเพิ่มเติม</a>
                        <a href="#" class="btn btn-primary">ใส่ตระกร้าเลย</a>
                    </div>
                </div>
            </div>
    
            <div class="col-md-3">
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center"></div>
                    <img src="../img/awp dlore.png" class="card-img-top" alt="..." style="width: 295px; height: 220px;">
                    <div class="card-body">
                        <h5 class="card-title">AWP Dragon Lore (FT)</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="ViewProduct.html" class="btn btn-warning">ดูเพิ่มเติม</a>
                        <a href="#" class="btn btn-primary">ใส่ตระกร้าเลย</a>
                    </div>
                </div>
            </div>
    
            <div class="col-md-3">
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center"></div>
                    <img src="../img/sporty_gloves_sporty_purple_light_large.56ae5ad710e069f040d86caa5fac952fd35dbe48.png" class="card-img-top" alt="..." style="width: 295px; height: 220px;">
                    <div class="card-body">
                        <h5 class="card-title">Pandora Box| Sport glove (FN)</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="ViewProduct.html" class="btn btn-warning">ดูเพิ่มเติม</a>
                        <a href="#" class="btn btn-primary">ใส่ตระกร้าเลย</a>
                    </div>
                </div>
            </div>
    
            <div class="col-md-3">
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center"></div>
                    <img src="../img/360fx360f.png" class="card-img-top" alt="..." style="width: 295px; height: 220px; ">
                    <div class="card-body">
                        <h5 class="card-title">AK-47 Vulcan (BS) </h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="ViewProduct.html" class="btn btn-warning">ดูเพิ่มเติม</a>
                        <a href="#" class="btn btn-primary">ใส่ตระกร้าเลย</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end of hot item -->

      <!-- footer -->
      <footer class="text-center mt-auto ">
    <div class="contact-info">
        <p>Contact us at: <span>info@smarthometech.com</span></p>
    </div>
    
</footer>

    <footer></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

</body>
</html>
