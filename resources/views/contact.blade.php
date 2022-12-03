<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Croxx Talent</title>
    @include('inc.head')
</head>
<body style="max-width: 98.5%">
    <section class="about-banner">
        @include('inc.navigation')
        <div class="container">
            <div class="row  slide">
                    <div class="slide-content-left">
                        <h1 class="text-center">Contact Us</h1>
                    </div>
            </div>
        </div>
    </section>
    <div class="row p-5">
    
        <div class="contact-form col-lg-7 col-sm-12 mt-5 p-5">
            <form action="{{ route('contact.post') }}" method="POST">
                @csrf
                <h3>Get in Touch</h3>
                <p>We'd love to hear from you. Please use the form below to get in touch with your questions, comments, or feedback.</p>
                <div class="row mt-2">
                    <div class="col-md-6">
                      <div class="form-group">
                        <input
                          required 
                          id="fullname"
                          type="text"
                          placeholder="Full Name"
                          name="fullname"
                        />
                      </div>
                    </div>
    
                    <div class="col-md-6">
                        <div class="form-group">
                          <input
                            required
                            id="emailAddress5"
                            type="email"
                            placeholder="Email"
                            name="emailAddress"
                          />
                        </div>
                      </div>
                  </div>
    
                  <div class="row mt-2">
                    <div class="col-md-6">
                      <div class="form-group">
                        <input
                          required
                          id="phoneNumeber"
                          type="text"
                          placeholder="Phone Number"
                          name="phoneNumber"
                        />
                      </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                          <input
                            required
                            id="subject"
                            type="text"
                            placeholder="Subject"
                            name="subject"
                          />
                        </div>
                      </div>
                  </div>
    
                  <div class="row mt-2">
                    <div class="col-md-12">
                      <div class="form-group">
                        <textarea
                          required
                          id="message"
                          name="message"
                          placeholder="Message"
                          rows="7"
                          maxlength="768"
                          class="form-control"
                        ></textarea>
                      </div>
                    </div>
                  </div>
                <button type="submit" class="btn btn-block btn-lg button-inverse">Submit</button>
            </form>
        </div>
    </div>
    <div class="container description">
        <div class="row card-container contact-cotainer">
                <div class="col content-card contact-card">
                    <img src="{{ mix('images/location.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                    <h3 class="">Address</h3>
                    <p class="text-muted text-left">
                        <i class="fas fa-map-marker-alt"></i>
                        315a Oscar, opposite Lopalace, Aghase Way, Lekki, Lagos State, Nigeria.
                    </p>
                </div>
                <div class="col content-card contact-card">
                    <img src="{{ mix('images/phone.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                    <h3 class="">Phone</h3>
                    <p class="text-muted">
                        <i class="fas fa-phone"></i>
                        +234 813 456 2234 <br>
                        <i class="fas fa-mobile"></i>
                        +234 813 456 2534 
                    </p>
                </div>
                <div class="col content-card contact-card">
                    <img src="{{ mix('images/mail.png') }}" class="mt-5" width="80" alt="Acountability Icon">
                    <h3 class="">Email</h3>
                    <p class="text-muted">
                        <i class="fas fa-envelope"></i>
                        Hello@croxxtalent.io </i><br>
                        <i class="fas fa-envelope"></i>
                        Support@croxxtalent.io 
                    </p>
                </div>
        </div>
        <div class="row contact-cotainer2">
                <div class="col-md-4">
                    <h3 class="mt-4">Address</h3>
                    <p class="text-muted text-left mb-4">
                        <i class="fas fa-map-marker-alt"></i>
                        315a Oscar, opposite Lopalace, Aghase Way, Lekki, Lagos State, Nigeria.
                    </p>
                </div>
                <div class="col-md-4">
                    <h3 class="mt-4 mb-4">Phone</h3>
                    <p class="text-muted mb-4">
                        <i class="fas fa-phone"></i>
                        +234 813 456 2234 <br>
                        <i class="fas fa-mobile"></i>
                        +234 813 456 2534 
                    </p>
                </div>
                <div class="col-md-4">
                    <h3 class="mt-4 mb-4">Email</h3>
                    <p class="text-muted mb-4">
                        <i class="fas fa-envelope"></i>
                        Hello@croxxtalent.com </i><br>
                        <i class="fas fa-envelope"></i>
                        Support@croxxtalent.com 
                    </p>
                </div>
        </div>
    </div>
    @include('inc.subscribe')
    @include('inc.footer')
</body>
</html>