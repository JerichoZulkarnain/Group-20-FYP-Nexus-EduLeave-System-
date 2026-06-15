<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus EduLeave System</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, sans-serif;
            scroll-behavior:smooth;
        }

        body{
            overflow-x:hidden;
        }

        /* TOP BAR */

        .topbar{
            width:100%;
            background:#d9e7f5;
            padding:12px;
            text-align:center;
            font-weight:bold;
            font-size:28px;
        }

        /* HERO SECTION */

        .hero{
            width:100%;
            height:100vh;
            background:
            linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)),
            url('photo.png');

            background-size:cover;
            background-position:center;
            position:relative;
            color:white;
        }

        /* NAVBAR */

        .navbar{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            padding:20px 50px;
        }

        .logo{
            width:240px;
        }

        .login-btn{
            background:white;
            color:black;
            text-decoration:none;
            padding:14px 35px;
            border-radius:40px;
            font-weight:bold;
            font-size:24px;
            transition:0.3s;
            margin-top:-5px; /* naikkan button */
        }

        .login-btn:hover{
            background:#ffe600;
        }

        /* HERO TEXT */

        .hero-content{
            position:absolute;
            top:52%;
            left:50px;
            transform:translateY(-50%);
            max-width:1150px;
        }

        .welcome{
            color:#ffe600;
            font-size:35px;
            font-weight:bold;
            margin-bottom:10px;
        }

        .title{
            font-size:90px;
            line-height:0.9;
            font-weight:900;
            color:#ffe600;
            margin-bottom:30px;
        }

        .description{
            font-size:24px;
            line-height:1.4;
            max-width:1200px;
        }

        /* LOGIN SECTION */

        .login-section{
            min-height:100vh;
            background:white;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            padding:80px 20px;
        }

        .login-title{
            font-size:55px;
            color:#0d2c63;
            margin-bottom:90px;
            text-align:center;
        }

        .login-container{
            display:flex;
            gap:120px;
            flex-wrap:wrap;
            justify-content:center;
            align-items:center;
        }

        .login-card{
            text-align:center;
            transition:0.3s;
        }

        .login-card:hover{
            transform:translateY(-10px);
        }

        .login-card img{
            width:200px;
            margin-bottom:20px;
        }

        .login-card a{
            text-decoration:none;
            color:black;
            font-size:32px;
            font-weight:bold;
        }

        /* RESPONSIVE */

        @media(max-width:900px){

            .topbar{
                font-size:18px;
            }

            .navbar{
                padding:20px;
            }

            .logo{
                width:150px;
            }

            .login-btn{
                font-size:18px;
                padding:12px 25px;
            }

            .hero-content{
                left:20px;
                right:20px;
            }

            .welcome{
                font-size:22px;
            }

            .title{
                font-size:55px;
            }

            .description{
                font-size:18px;
            }

            .login-title{
                font-size:38px;
            }

            .login-card img{
                width:140px;
            }

            .login-card a{
                font-size:24px;
            }

        }

    </style>

</head>

<body>

    <!-- TOP BAR -->

    <div class="topbar">
        CONTACT US: NEXUSEDULEAVE@GMAIL.COM
    </div>

    <!-- HERO SECTION -->

    <section class="hero">

        <div class="navbar">

            <!-- LOGO -->
            <img src="logo.png" class="logo">

            <!-- LOGIN BUTTON -->
            <a href="#login-section" class="login-btn">
                LOG IN
            </a>

        </div>

        <!-- HERO CONTENT -->

        <div class="hero-content">

            <div class="welcome">
                WELCOME TO
            </div>

            <div class="title">
                NEXUS <br>
                EDULEAVE SYSTEM
            </div>

            <div class="description">
                Nexus EduLeave System is an advanced digital platform designed to streamline and manage student leave applications efficiently within Nexus International University Malaysia. The system provides a user-friendly interface that enables students to submit leave requests while allowing lecturers and administrators to review, approve, and track applications in a structured and transparent manner.
            </div>

        </div>

    </section>

    <!-- LOGIN SECTION -->

    <section class="login-section" id="login-section">

        <h1 class="login-title">
            Nexus EduLeave System Logins
        </h1>

        <div class="login-container">

            <!-- STUDENT -->

            <div class="login-card">

                <img src="STUDENT.png">

                <br>

                <a href="login.php">
                    STUDENT →
                </a>

            </div>

            <!-- LECTURER -->

            <div class="login-card">

                <img src="LECTURER.png">

                <br>

                <a href="login.php">
                    LECTURER →
                </a>

            </div>

            <!-- ADMIN -->

            <div class="login-card">

                <img src="ADMIN.png">

                <br>

                <a href="login.php">
                    ADMINISTRATIVE
                </a>

            </div>

        </div>

    </section>

</body>
</html>