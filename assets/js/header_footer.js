$(document).ready(function () {

  // arabic header 
  $('#header').html(`
    <header  class="header d-flex align-items-center fixed-top" dir="ltr">
    <div
        class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

        <a href="index.html" class="logo d-flex align-items-center me-auto me-xl-0">
            <img src="assets/img/logo_light.png" alt="Designer Logo" class="img-fluid  "
                style="width: 30px;margin-right: 4px;">
            <h1 class="sitename">Digital Art</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li>
                    <a type="button" class="login-icon d-block d-sm-none justify-content-start"
                        style="font-weight: lighter;">

                        <svg class="" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg> Log in
                    </a>
                </li>
                <li><a href="#hero" class="active">الرئيسية</a></li>
                <li><a href="#about">من نحن</a></li>
                <li><a href="#features">المميزات</a></li>
                <li><a href="#services">الخدمات</a></li>
                <li><a href="#pricing">الأسعار</a></li>
                <li><a href="contact.html">تواصل معنا</a></li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
        <span><a type="button" class="login-icon d-none d-md-inline-block" style="font-weight: lighter;">
                <span class="" style="padding: 0 10px 0 0;font-weight: lighter;">|</span>
                <svg class="" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg> Log in
            </a>
            <a class="btn-getstarted" href="en.html"> En </a>
        </span>

    </div>
</header>
      `);



  $('#footer').html(`
      <footer class="footer">
      <!-- Footer Top -->
      <div class="footer-top">
        <div class="container">
          <div class="row">
            <div class="col-lg-3 col-md-6 col-12">
              <div class="single-footer">
              <h2>من نحن</h2>
              <p>
                شركة بريستيل لتصنيع مكونات الأطراف الاصطناعية والمساند التقويمية.
                <br>
                تنتج بريستيل مجموعة واسعة من مكونات الأطراف الاصطناعية من التيتانيوم والفولاذ المقاوم للصدأ .
                <br>
              </p>
              <!-- Social -->
                <ul class="social">
                  <li>
                    <a href="https://wa.me/00905386610079"><i class="icofont-whatsapp"></i></a>
                  </li>
                  <li>
                    <a href="https://www.facebook.com/prsteel.co"><i class="icofont-facebook"></i></a>
                  </li>
                  <li>
                    <a href="https://www.instagram.com/prsteel.co"><i class="icofont-instagram"></i></a>
                  </li>
                  <li>
                    <a href="#"><i class="icofont-youtube-play"></i></a>
                  </li>
                  <li>
                    <a href="mailto:support@yourmail.com"><i class="icofont-email"></i></a>
                  </li>
                </ul>
                <!-- End Social -->
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
              <div class="single-footer f-link">
                <h2>روابط سريعة </h2>
                <div class="col-lg-6 col-md-6 col-12">
                  <ul>
                    <li>
                      <a href="ar.html"><i class="fa fa-caret-left" aria-hidden="true"></i>الرئيسية</a>
                    </li>
                    <li>
                      <a href="ar_about.html"><i class="fa fa-caret-left" aria-hidden="true"></i>من نحن</a>
                    </li>
                    <li>
                      <a href="ar_products_category.html"><i class="fa fa-caret-left" aria-hidden="true"></i>منتجاتنا</a>
                    </li>
                    <li>
                      <a href="ar_contact.html"><i class="fa fa-caret-left" aria-hidden="true"></i>اتصل بنا</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
              <div class="single-footer">
                <h2>معلومات الاتصال</h2>
                <p>
                  إذا كان لديك أي أسئلة فلا تتردد بالاتصال بنا.
                </p>
                <br>
                <p> مرسين, تركيا <i class="fa fa-home mr-3 mb-4"></i></p>
                <p><a href="info@pr-steel.com">  info@pr-steel.com </a> <i class="fa fa-envelope me-3 mb-4"></i> </p>
                
                
                <p style="color: #d9ff00;"> مسؤول المبيعات داخل تركيا</p>
                <p> <span dir="ltr"> +90 543 336 77 54 </span> <i class="fa fa-phone me-3"></i></p>
                <br>
                <p style="color: #d9ff00;">مسؤول المبيعات خارج تركيا </p>
                <p> <span dir="ltr"> +90 538 661 00 79 </span> <i class="fa fa-phone me-3"></i></p>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
              <div class="single-footer">
                <img src="qr-code.png" alt="prsteel qr code">
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ End Footer Top -->
      <!-- Copyright -->
      <footer id="Copyright" class="Copyright text-white text-center py-3" style="
      background-color: #181818;">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-auto">
              <p class="mb-0">© 2024 Copyright |
                <a href="https://digital-art.website" target="_blank" class=" align-items-center  " style="
                padding-inline-start: 1rem;color: #09a7c7;">
                  <img src="img/digital-art.png" alt="Designer Logo" class="img-fluid  "
                    style="width: 30px;margin-right: 4px;">
                  <span class="designer-name"> Digital Art </span>
                </a>
              </p>
  
            </div>
          </div>
  
        </div>
      </footer>
  
  
  
  
      <!--/ End Copyright -->
    </footer>
      `);
});
