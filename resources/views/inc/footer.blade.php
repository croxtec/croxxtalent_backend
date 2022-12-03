
    {{-- Footer section  --}}
    <section class="footer-section">
        <div class="container">
            <div class="row mt-3 mb-4">
                <div class="col-md-3">
                    <div class="row ml-1">
                        <img src="{{ mix('images/logo.png') }}" alt="Croxx Talent" style="max-width: 250px;" class="mb-4 img-fluid">
                    </div>
                    <div class="row ml-1">
                        <p> &copy; Copyright 2021 Croxx Talent <br> All Rights Reserved</p>
                    </div>
                </div>
                <div class="col-md-3 footer-links">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                        <li><a href="{{ route('about') }}">About</a></li>
                    </ul>
                </div>
                <div class="col-md-3 footer-links">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}">Terms of Use</a></li>
                    </ul>
                </div>
                <div class="col-md-3 social-icons">
                    <h4>Connect</h4>
                    <a href="">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="">
                        <i class="fab fa-google"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <script src="{{ mix('js/app.js') }}"></script>
