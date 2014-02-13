<!doctype html>
<html lang="en">
<head>
    <title>Lifeline Bookfair Information System</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS are placed here -->

    @section('styles')
    @show
</head>
<body>
    <!-- Container -->
    <div class="container">
      <!-- Content -->
      @yield('content')
    </div>

    <!-- Scripts are placed here -->
	@section('scripts')
    @show
</body>
</html>