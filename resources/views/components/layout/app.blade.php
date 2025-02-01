<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SPBE - Kota kendari</title>
  <meta name="description" content="Selamat datang di SPBE Kota Kendari">
  <meta name="keywords" content="spbe, kota kendari">

  <x-head />
</head>

<body class="index-page">
  <x-header />
  {{ $slot }}
  <x-footer />

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <x-script />
</body>

</html>
