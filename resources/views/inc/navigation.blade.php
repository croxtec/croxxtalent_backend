<div class="header-container header-contain hide-menu">
    <div class="container">
        <div class="row">
            <div class="col-3 header-logo">
                <a href="{{ route('home') }}">
                    <img src="{{ mix('images/logo.png') }}" alt="Croxx Talent" width="220">
                </a>
            </div>
            <div class="col-5 nav-menu">
                 <div class="dropdown">
                    <div class="com-menu" id="drop" onclick="dropShow()"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                       Company
                        <i id="drop-icon" class="pl-2 fa fa-chevron-down" style="font-size: 12px;"></i>
                    </div>
                    <div class="dropdown-menu" id="dpMenu" aria-labelledby="dpMenu">
                        <a class="dropdown-item" href="{{ route('home') }}">Home</a>
                        <a class="dropdown-item" href="{{ route('about') }}">About</a>
                        <a class="dropdown-item" href="{{ route('contact') }}">Contact</a>
                    </div>
                </div>
            </div>
            <div class="col-4 header-btn">
                <div class="row">
                    <div class="col p-1 ">
                        <a class="btn button-direct btn-md" id="loginMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >Login</a>
                        <div class="dropdown-menu" aria-labelledby="loginMenu">
                            <!-- dropdown-toggle -->
                            <a class="dropdown-item" href="{{ route('login') }}">Talent</a>
                            <a class="dropdown-item" href="{{ url('app/login/employer') }}">Employer</a>
                            <a class="dropdown-item" href="{{ url('app/login/affilate') }}">Affilate</a>
                        </div>
                        <!-- <div class="dropdown"> </div> -->
                    </div>
                    <div class="col p-1 " style="flex-grow: 1.4">
                        <a class="btn button-inverse btn-md d-none d-md-block" href="{{ route('register') }}">Free Sign Up</a>
                    </div>
                    <div class="col p-2 show-mobile"><i class="fas fa-bars"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="header-container show-menu hide">
    <div class="container">
        <div class="row">
            <div class="col-6 header-logo">
                <img src="{{ mix('images/logo.png') }}" alt="Croxx Talent" width="220">
            </div>
            <div class="col-6 header-btn">
                <div class="row">
                    <div class="col p-2">
                        <a class="btn button-direct btn-md" href="{{ route('login') }}">Login</a>
                    </div>
                    <!-- <div class="col p-2">
                        <a class="btn button-inverse btn-md" href="{{ route('register') }}">Free Sign Up</a>
                    </div> -->
                    <div class="col p-2 hide-mobile"><i class="fas fa-times"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mobile-menu">
        <ul>
            <li class="active-mobile"><a href="{{ route('home') }}" >Home</a></li>
            <li><a href="{{ route('about') }}" >About</a></li>
            <li><a href="{{ route('contact') }}" >Contact</a></li>
        </ul>
    </div>
</div>
<script>
    function dropShow(){
        var dpMenu = document.getElementById('dpMenu');
        var icon = document.getElementById('drop-icon');
        var drop = document.getElementById('drop');
        if(dpMenu.classList.contains("show")){
            drop.classList.remove("active");
            icon.classList.add("fa-chevron-down");
            icon.classList.remove("fa-chevron-up");
        }else{
            drop.classList.add("active");
            icon.classList.remove("fa-chevron-down");
            icon.classList.add("fa-chevron-up");
        }
    }
</script>
