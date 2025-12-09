<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mini Chat – Connect Privately</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style> 
* {
    margin: 0; padding: 0; box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}


body {
    background: url("https://media.istockphoto.com/id/1262582481/vector/chat-messages-smartphone-sms-on-mobile-phone-screen-man-woman-couple-chatting-messaging.jpg?s=1024x1024&w=is&k=20&c=9ekzDJq4_6FCNDOPF9yo8Y5MlARfFUA9PrPJP25d9Zg=") no-repeat center center/cover;
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    position: relative;
}

body::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.55);
}


.hero {
    position: relative;
    z-index: 2;
    width: 90%;
    max-width: 450px;
    text-align: center;
    backdrop-filter: blur(15px);
    background: rgba(255,255,255,0.15);
    padding: 40px 30px;
    border-radius: 22px;
    color: #fff;
    box-shadow: 0 4px 25px rgba(0,0,0,0.4);
}

.hero h1 {
    font-size: 38px;
    margin-bottom: 10px;
    font-weight: 700;
}
.hero p {
    font-size: 17px;
    opacity: 0.9;
}

.buttons {
    margin-top: 35px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
}

.btn {
    background: linear-gradient(45deg, #00c6ff, #0084ff);
    color: white;
    font-weight: 600;
    padding: 12px 35px;
    border-radius: 50px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    outline: none;
    font-size: 16px;
    transition: 0.3s ease-in-out;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}
.btn:hover {
    transform: scale(1.06);
    background: linear-gradient(45deg, #0084ff, #00c6ff);
}

.support-icon, .whatsapp-icon {
    position: fixed;
    width: 55px; height: 55px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    z-index: 100;
}

.support-icon {
    bottom: 20px;
    left: 20px;
    background: #ffffff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.support-icon i { font-size: 25px; color: #0084ff; }

.support-box {
    position: fixed;
    bottom: 90px;
    left: 20px;
    background: #ffffff;
    color: #000;
    padding: 12px 15px;
    border-radius: 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.3);
    font-size: 14px;
    width: 230px;
    display: none;
    z-index: 100;
}
.support-box p {
    margin: 6px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.support-box a {
    text-decoration: none;
    color: #000;
}
.support-box a:hover {
    color: #0084ff;
}

.whatsapp-icon {
    bottom: 20px;
    right: 20px;
    background: #25D366;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
}
.whatsapp-icon i {
    color: white;
    font-size: 28px;
}

@media(max-width: 600px) {
    .hero h1 { font-size: 30px; }
    .hero p { font-size: 15px; }
}
</style>
</head>

<body>

<div class="hero">
    <h1>Mini Two-Way Chat</h1>
    <p>Fast • Secure • Real-time messaging like WhatsApp</p>

    <div class="buttons">
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
    </div>
</div>

<div class="support-icon" onclick="toggleSupport()">
    <i class="fa-solid fa-headset"></i>
</div>

<div class="support-box" id="supportBox">
    <p><i class="fa fa-phone"></i> <a href="tel:+917569398385">+91 75693 98385</a></p>
    <p><i class="fa fa-envelope"></i> <a href="mailto:y22cm171@rvrjc.ac.in">y22cm171@rvrjc.ac.in</a></p>
    <p><i class="fab fa-whatsapp"></i> <a href="https://wa.me/917569398385?text=Hello%20support%20needed" target="_blank">WhatsApp Support</a></p>
</div>

<a href="https://wa.me/917569398385?text=Hello%20support%20needed" target="_blank">
    <div class="whatsapp-icon">
        <i class="fab fa-whatsapp"></i>
    </div>
</a>

<script>
function toggleSupport() {
    var box = document.getElementById('supportBox');
    box.style.display = (box.style.display === "block") ? "none" : "block";
}
</script>

</body>
</html>