@include('inc.message')
<section class="subscribe-section">
    <div class="container">
        <form action="{{ route('subscribe')}}" method="post">
            @csrf
            <div class="row">
                <div class="col">
                    <h3>Join our mailing list</h3>
                    <div class="input-group mb-3 subscribe-input">
                        <input type="text" name="email" class="form-control" placeholder="Input Your Email Address">
                        <div class="input-group-prepend">
                            <button class="btn btn-lg btn-subscribe" type="submit">Subscribe</button>
                        </div>
                    </div>
                    <p>Stay updated with CroxxTalent latest news and info </p>
                </div>
            </div>
        </form>
    </div>
</section>