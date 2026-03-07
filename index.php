
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ensemble Lin - Livraison en Algérie</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container product-page-container">
<?php
require_once 'config.php';

// التعامل مع الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // قراءة البيانات
  $name     = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
  $wilaya   = isset($_POST['wilaya']) ? htmlspecialchars($_POST['wilaya']) : '';
  $address  = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '';
  $phone    = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
  $color    = isset($_POST['color']) ? htmlspecialchars($_POST['color']) : '';
  $size     = isset($_POST['size']) ? htmlspecialchars($_POST['size']) : '';
  $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

  $unit_price = 5900;
  $price = ($unit_price * $quantity) ;

  // التحقق من صحة البيانات
  if (empty($name) || empty($wilaya) || empty($address) || empty($phone) || empty($color) || empty($size) || $quantity < 1) {
    echo '<div class="error-message">الرجاء ملء جميع الحقول المطلوبة.</div>';
  } elseif (!preg_match('/^0[5-7][0-9]{8}$/', $phone)) {
    echo '<div class="error-message">رقم الهاتف غير صحيح. الرجاء إدخال رقم هاتف جزائري صحيح.</div>';
  } else {
    // إدخال البيانات في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO orders (name, phone, color, size, quantity, wilaya, address, price, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("ssssissd", $name, $phone, $color, $size, $quantity, $wilaya, $address, $price);
    
    if ($stmt->execute()) {
      echo '<div class="success-message">شكراً لطلبك! تم حفظ الطلب بنجاح.</div>';
    } else {
      echo '<div class="error-message">حدث خطأ أثناء حفظ الطلب: ' . $conn->error . '</div>';
    }
    $stmt->close();
  }
}
?>

    <div class="product-relative-wrapper">
      <div class="badge">PROMO -21%</div>
      
      
      <!-- Product Section -->
      <div class="product-layout">
        <!-- Left: Product Images -->
        <div class="product-half">
          <div class="carousel">
            <div class="carousel-inner" id="carouselInner">
              <div class="carousel-item"><img src="src/white.jpg" alt="Ensemble lin blanc"></div>
              <div class="carousel-item"><img src="src/black.jpg" alt="Ensemble lin noir"></div>             
              <div class="carousel-item"><img src="src/brown.jpg" alt="Ensemble lin brown"></div>
              <div class="carousel-item"><img src="src/grey.jpg" alt="Ensemble lin gris"></div>
            </div>
            <div class="carousel-nav" id="carouselNav"></div>
            <div class="carousel-prev" id="prevBtn"><i class="fas fa-chevron-left"></i></div>
            <div class="carousel-next" id="nextBtn"><i class="fas fa-chevron-right"></i></div>
          </div>
        </div>
        
        <!-- Right: Product Details -->
        <div class="product-half">
          <h1 class="product-title">طقم كتان رجالي</h1>
          <p class="product-subtitle">قميص و سروال من الكتان لراحة لاناقة و راحة استثنائية</p>
          <div class="price-container">
            <span class="price">5,900 DA</span>
            <span class="original-price">7,500 DA</span>
            <span class="discount">-21%</span>
          </div>
          
          <div class="promo-box">
            <div class="promo-title">عرض محدود! هذا العرض ينتهني بعد:</div>
            <div class="countdown" id="countdown">
              <div class="countdown-item">
                <span class="countdown-number" id="days">02</span>
                <span class="countdown-label">ايام</span>
              </div>
              <div class="countdown-item">
                <span class="countdown-number" id="hours">11</span>
                <span class="countdown-label">ساعات</span>
              </div>
              <div class="countdown-item">
                <span class="countdown-number" id="minutes">45</span>
                <span class="countdown-label">دقائق</span>
              </div>
              <div class="countdown-item">
                <span class="countdown-number" id="seconds">22</span>
                <span class="countdown-label">ثواني</span>
              </div>
            </div>
          </div>
          
          <div class="section-margin">
            <h3 class="section-title">الخصائص:</h3>
            <div class="feature">
              <i class="fas fa-check-circle"></i>
              <span>كتان اصلي طبيعي %100</span>
            </div>
            <div class="feature">
              <i class="fas fa-check-circle"></i>
              <span>مريح و ملائم للصيف</span>
            </div>
            <div class="feature">
              <i class="fas fa-check-circle"></i>
              <span>تصميم عصري و انيق</span>
            </div>
            <div class="feature">
              <i class="fas fa-check-circle"></i>
              <span>سهل العناية و الغسيل</span>
            </div>
            <div class="feature">
              <i class="fas fa-check-circle"></i>
              <span>توصيل كل الولايات</span>
            </div>
          </div>
          
  <!-- Color Selection -->
  <!-- <div class="section-margin">
    <h3 class="section-title">الالوان:</h3>
    <div class="color-options">
      <div class="color-option selected" data-color="noir" data-slide="0" style="background-color: black;"></div>
      <div class="color-option" data-color="beige" data-slide="1" style="background-color: rgb(218, 218, 160);"></div>
      <div class="color-option" data-color="camel" data-slide="2" style="background-color: #c19a6b;"></div>
      <div class="color-option" data-color="camel" data-slide="2" style="background-color: #c19a6b;"></div>
    </div>
  </div> -->

  <div class="section-margin section-top-margin">
      <h3 class="section-title">الالوان:</h3>
      <div class="color-options gap-sm">
        <div class="color-option selected" data-slide="0" data-color="white" style="background-color: white; border: 1px solid #ccc;"></div>
        <div class="color-option" data-slide="1" data-color="black" style="background-color: black;"></div>
        <div class="color-option" data-slide="2" data-color="brown" style="background-color: brown;"></div>
        <div class="color-option" data-slide="3" data-color="grey" style="background-color: grey;"></div>
      </div>
    </div>

  <!-- Size Selection -->
  <div class="section-margin">
    <h3 class="section-title">المقاس:</h3>
    <div class="size-options">
      <div class="size-option selected" data-size="M">M</div>
      <div class="size-option" data-size="L">L</div>
      <div class="size-option" data-size="XL">XL</div>
    </div>
  </div>

  <!-- Quantity Selection -->
  <div class="section-margin">
    <h3 class="section-title">الكمية:</h3>
    <input
      type="number"
      id="quantityInput"
      min="1"
      value="1"
      class="quantity-input"
      style="width: 100px;"
      required
    >
  </div>

        </div>
      </div>
    </div>
    
    <!-- Order Form -->
    <div class="form-container order-section">
      <h2 class="form-title">اطلب الان</h2>
      <form id="orderForm" method="POST" action="">
        <input type="hidden" id="colorInput" name="color" value="noir">
        <input type="hidden" id="sizeInput" name="size" value="M">
        <input type="hidden" id="quantityHidden" name="quantity" value="1">

        <div class="form-group">
          <label class="form-label" for="name">الاسم:</label>
          <input type="text" id="name" name="name" class="form-input" placeholder="ادخل اسمك هنا" required>
        </div>
        
        <div class="form-group">
          <label class="form-label" for="wilaya">الولاية:</label>
          <select id="wilaya" name="wilaya" class="form-select" required>
            <option value="">-- اختر ولايتك --</option>
            <option value="Adrar">Adrar</option>
            <option value="Chlef">Chlef</option>
            <option value="Laghouat">Laghouat</option>
            <option value="Oum El Bouaghi">Oum El Bouaghi</option>
            <option value="Batna">Batna</option>
            <option value="Béjaïa">Béjaïa</option>
            <option value="Biskra">Biskra</option>
            <option value="Béchar">Béchar</option>
            <option value="Blida">Blida</option>
            <option value="Bouira">Bouira</option>
            <option value="Tamanrasset">Tamanrasset</option>
            <option value="Tébessa">Tébessa</option>
            <option value="Tlemcen">Tlemcen</option>
            <option value="Tiaret">Tiaret</option>
            <option value="Tizi Ouzou">Tizi Ouzou</option>
            <option value="Alger">Alger</option>
            <option value="Djelfa">Djelfa</option>
            <option value="Jijel">Jijel</option>
            <option value="Sétif">Sétif</option>
            <option value="Saïda">Saïda</option>
            <option value="Skikda">Skikda</option>
            <option value="Sidi Bel Abbès">Sidi Bel Abbès</option>
            <option value="Annaba">Annaba</option>
            <option value="Guelma">Guelma</option>
            <option value="Constantine">Constantine</option>
            <option value="Médéa">Médéa</option>
            <option value="Mostaganem">Mostaganem</option>
            <option value="M'Sila">M'Sila</option>
            <option value="Mascara">Mascara</option>
            <option value="Ouargla">Ouargla</option>
            <option value="Oran">Oran</option>
            <option value="El Bayadh">El Bayadh</option>
            <option value="Illizi">Illizi</option>
            <option value="Bordj Bou Arreridj">Bordj Bou Arreridj</option>
            <option value="Boumerdès">Boumerdès</option>
            <option value="El Tarf">El Tarf</option>
            <option value="Tindouf">Tindouf</option>
            <option value="Tissemsilt">Tissemsilt</option>
            <option value="El Oued">El Oued</option>
            <option value="Khenchela">Khenchela</option>
            <option value="Souk Ahras">Souk Ahras</option>
            <option value="Tipaza">Tipaza</option>
            <option value="Mila">Mila</option>
            <option value="Aïn Defla">Aïn Defla</option>
            <option value="Naâma">Naâma</option>
            <option value="Aïn Témouchent">Aïn Témouchent</option>
            <option value="Ghardaïa">Ghardaïa</option>
            <option value="Relizane">Relizane</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label" for="address">العنوان:</label>
          <input type="text" id="address" name="address" class="form-input" placeholder="عنوان التوصيل" required>
        </div>
        
        <div class="form-group">
          <label class="form-label" for="phone">رقم الهاتف:</label>
          <input type="tel" id="phone" name="phone" class="form-input" placeholder="Ex: 0551234567" pattern="^0[5-7][0-9]{8}$" required>
        </div>
        
        <button type="submit" class="btn-primary">
          <i class="fas fa-shopping-bag mr-2"></i> اطلب الان
        </button>
      </form>
    </div>
    
    <!-- Trust Badges -->
    <div class="trust-badges mt-12">
      <div class="trust-badge">
        <i class="fas fa-truck"></i>
        <span class="trust-badge-text">توصيل سريع</span>
      </div>
      <div class="trust-badge">
        <i class="fas fa-money-bill-wave"></i>
        <span class="trust-badge-text">الدفع عند الاستلام</span>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="assets/js/main.js"></script>
</body>
</html>
