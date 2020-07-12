<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div id="app">
    <h1>{{$concert->title}}</h1>
    <h2>{{$concert->subtitle}}</h2>
    <p>{{$concert->formatted_date}}</p>
    <p>Doors at {{$concert->formatted_start_time}}</p>
    <p>{{$concert->price_in_dollars}}</p>
    <p>{{$concert->venue}}</p>
    <p>{{$concert->venue_address}}</p>
    <p>{{$concert->city}}, {{$concert->state}} {{$concert->zip}}</p>
    <p>{{$concert->additional_information}}</p>

    <ticket-checkout price="{{$concert->ticket_price}}"
                     concert-title="{{$concert->title}}"
                     concert-id="{{$concert->id}}"/>
</div>
<script>
    window.App = {
        csrfToken: '{{ csrf_token() }}',
        stripePublicKey: {{config('services.stripe.public_key')}},
    }
</script>
<script src="https://checkout.stripe.com/checkout.js"></script>
<script src="{{mix('js/app.js')}}"></script>
</body>
</html>
