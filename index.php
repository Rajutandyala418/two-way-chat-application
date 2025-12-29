<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mini Chat – Connect Privately</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

html,body{
    width:100%;
    height:100%;
}

body{
    background-image:url("https://files.ably.io/ghost/prod/2023/06/the-ultimate-guide-to-chat-app-architecture.png");
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;
    background-attachment:fixed;
    display:flex;
    align-items:center;
    justify-content:center;
}

.hero{
    width:92%;
    max-width:450px;
    text-align:center;
    background:rgba(0,0,0,0.55);
    backdrop-filter:blur(14px);
    padding:45px 30px;
    border-radius:25px;
    color:#fff;
    box-shadow:0 6px 30px rgba(0,0,0,0.45);
    transition:transform 0.3s ease;
}

.hero:hover{
    transform:scale(1.02);
}

.hero h1{
    font-size:36px;
    font-weight:700;
    margin-bottom:10px;
}

.hero p{
    font-size:17px;
    opacity:0.95;
}

.buttons{
    margin-top:35px;
    display:flex;
    gap:18px;
    flex-wrap:wrap;
    justify-content:center;
}

.btn{
    background:linear-gradient(45deg,#00c6ff,#0084ff);
    color:#fff;
    font-weight:600;
    padding:13px 38px;
    border-radius:50px;
    text-decoration:none;
    font-size:16px;
    transition:0.3s ease;
    box-shadow:0 4px 12px rgba(0,0,0,0.4);
}

.btn:hover{
    transform:scale(1.08);
}

.support-icon,
.whatsapp-icon{
    position:fixed;
    width:58px;
    height:58px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    z-index:200;
}

.support-icon{
    bottom:20px;
    left:20px;
    background:#fff;
    box-shadow:0 4px 15px rgba(0,0,0,0.35);
}

.support-icon i{
    font-size:26px;
    color:#0084ff;
}

.support-box{
    position:fixed;
    bottom:90px;
    left:20px;
    background:#fff;
    color:#000;
    padding:14px 16px;
    border-radius:16px;
    width:235px;
    display:none;
    box-shadow:0 3px 12px rgba(0,0,0,0.3);
    z-index:200;
}

.support-box p{
    margin:7px 0;
    display:flex;
    align-items:center;
    gap:8px;
    font-size:15px;
}

.support-box a{
    text-decoration:none;
    color:#000;
}

.support-box a:hover{
    color:#0084ff;
}
@media(max-width:480px){
    body{
        background-attachment:scroll;
    }
    .hero{
        padding:35px 20px;
        border-radius:20px;
    }
    .hero h1{
        font-size:28px;
    }
    .hero p{
        font-size:15px;
    }
    .btn{
        width:100%;
        padding:12px 0;
        font-size:15px;
    }
    .support-icon,
    .whatsapp-icon{
        width:50px;
        height:50px;
    }
}
</style>
</head>

<body>

<div class="hero">
    <h1>Mini Two-Way Chat</h1>
    <p>Fast • Secure • Real-time messaging App</p>

    <div class="buttons">
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
    </div>
</div>

<div class="support-icon" id="supportIcon">
    <i class="fa-solid fa-headset"></i>
</div>

<div class="support-box" id="supportBox">
    <p><i class="fa fa-phone"></i> <a href="tel:+917569398385">+91 75693 98385</a></p>
    <p><i class="fa fa-envelope"></i> <a href="mailto:y22cm171@rvrjc.ac.in">y22cm171@rvrjc.ac.in</a></p>
    <p><i class="fab fa-whatsapp"></i> <a href="https://wa.me/917569398385" target="_blank">WhatsApp Support</a></p>
</div>

<script>
const supportIcon = document.getElementById("supportIcon");
const supportBox = document.getElementById("supportBox");

supportIcon.addEventListener("click", e => {
    e.stopPropagation();
    supportBox.style.display = supportBox.style.display === "block" ? "none" : "block";
});

document.addEventListener("click", e => {
    if (!supportBox.contains(e.target) && !supportIcon.contains(e.target)) {
        supportBox.style.display = "none";
    }
});
</script>

</body>
</html>
