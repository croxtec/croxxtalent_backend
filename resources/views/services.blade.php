<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Croxx Talent</title>
    @include('inc.head')
</head>
<body>
    <section class="about-banner">
        @include('inc.navigation')
        <div class="container">
            <div class="row  slide">
                    <div class="slide-content-left">
                        <h1 class="text-center">Services</h1>
                    </div>
            </div>
        </div>
    </section>
    <div class="container p-5 about-content">
        
        <h2 class="mb-4 text-center"> We’re on a mission </h2>
        <div class="row">
            <div class="col-md-6  order-sm-12 pl-5 info-img mb-3">
                    <img src="{{ mix('images/about.png') }}" alt="Services" width="400">
            </div>
            <div class="col-md-6  order-sm-1 about-conten-paragraph">
                <div class="text-muted">
                    <p>Africa’s leading oil and gas talent force
                        Croxxtalent began 2020 by pioneering a better way of manpower contract hire in the oil and gas sector. 
                        Helping companies find flexibility and connecting talents with more opportunities.
                    </p>
                    <p>Our mission is to create economic opportunities for both talents and companies 
                        looking to work together in a progressive environment.</p>
                    <p>We hope to create more job opportunities for skilled workers who have no direct access to 
                        getting hired by top engineering companies
                    Join us on our journey and see how we could be of help to your career and company.
                    </p>
                    <a href="{{ route('register') }}"> <button class="btn button-inverse btn-lg mt-4 mb-2">Get Started</button></a> <br>
                    <p class="text-muted">Already a member?<a href="{{ route('login') }}"> Sign In</a></p>
                </div>
            </div>
            
        </div>
    </div>
    <section class="info-section">
        <div class="container">
            <div class="row info-sub-section">
                <div class="info-img col-sm-6">
                    <img src="{{ mix('images/about3.png') }}" alt="employee_search"  width="300">
                </div>
                <div class="col-sm-6 info-content">
                    <h3>Looking For Talent?</h3>
                    <p class="text-muted">
                        Croxxtalent boasts of the best skilled workers. Sign up as an employers and go through our robust talent database. 
                        <br><a href="{{ route('register') }}"> <button class="btn btn-green button-inverse btn-lg my-3">Sign Up</button></a> 
                    </p>
                </div>
            </div>
            <div class="row pt-5 info-sub-section">
                <div class="info-img col-md-6 order-sm-12">
                    <img src="{{ mix('images/about2.png') }}" alt="employer_search" width="350">
                </div>
                <div class="col-md-6  order-sm-1 info-content">
                    <h3>Get hired!</h3>
                    <p class="text-muted">
                        Looking to get hired by top engineering companies? Look no further. Create your CV and start applying for jobs. 
                        <br><a href="{{ route('register') }}"> <button class="btn button-inverse btn-lg my-3">Create CV</button></a> 
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- <section class="video-section">
        <div class="container">
            <div class="row">
                <div class="col">
                    <iframe width="100%" height="570" src="https://www.youtube.com/embed/U0gVbw2Ynwo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
            </div>
        </div>
    </section> -->
    @include('inc.subscribe')
    @include('inc.footer')
</body>
</html>