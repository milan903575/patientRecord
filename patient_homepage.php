<?php
include 'connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: login.html");
    exit;
}

$patient_id = $_SESSION['user_id'];

$sql_patient = "SELECT * FROM patients WHERE id = ?";
$stmt_patient = $conn->prepare($sql_patient);
$stmt_patient->bind_param("i", $patient_id);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
$patient = $result_patient->fetch_assoc();
$stmt_patient->close();

if (isset($patient['date_of_birth'])) {
    $dob_date = new DateTime($patient['date_of_birth']);
    $now = new DateTime();
    $age = $now->diff($dob_date)->y;
} else {
    $age = 'N/A';
}

$conn->close();
?>
<section class="showcase">
<html>
<head>

<style>
@import url('https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800,900&display=swap');
*
{
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}
header
{
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  padding: 40px 100px;
  z-index: 1000;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
header .logo
{
  color: #fff;
  text-transform: uppercase;
  cursor: pointer;
}
.toggle
{
  position: relative;
  width: 60px;
  height: 60px;
  background: url(https://i.ibb.co/HrfVRcx/menu.png);
  background-repeat: no-repeat;
  background-size: 30px;
  background-position: center;
  cursor: pointer;
}
.toggle.active
{
  background: url(https://i.ibb.co/rt3HybH/close.png);
  background-repeat: no-repeat;
  background-size: 25px;
  background-position: center;
  cursor: pointer;
}
.showcase
{
  position: absolute;
  right: 0;
  width: 100%;
  min-height: 100vh;
  padding: 100px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #111;
  transition: 0.5s;
  z-index: 2;
}
.showcase.active
{
  right: 300px;
}

.showcase video
{
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.8;
}
.overlay
{
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: #03a9f4;
  mix-blend-mode: overlay;
}
.text
{
  position: relative;
  z-index: 10;
}

.text h2
{
  font-size: 5em;
  font-weight: 800;
  color: #fff;
  line-height: 1em;
  text-transform: uppercase;
}
.text h3
{
  font-size: 4em;
  font-weight: 700;
  color: #fff;
  line-height: 1em;
  text-transform: uppercase;
}
.text p
{
  font-size: 1.1em;
  color: #fff;
  margin: 20px 0;
  font-weight: 400;
  max-width: 700px;
}
.text a
{
  display: inline-block;
  font-size: 1em;
  background: #fff;
  padding: 10px 30px;
  text-transform: uppercase;
  text-decoration: none;
  font-weight: 500;
  margin-top: 10px;
  color: #111;
  letter-spacing: 2px;
  transition: 0.2s;
}
.text a:hover
{
  letter-spacing: 6px;
}
.social
{
  position: absolute;
  z-index: 10;
  bottom: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}
.social li
{
  list-style: none;
}
.social li a
{
  display: inline-block;
  margin-right: 20px;
  filter: invert(1);
  transform: scale(0.5);
  transition: 0.5s;
}
.social li a:hover
{
  transform: scale(0.5) translateY(-15px);
}
.menu
{
  position: absolute;
  top: 0;
  right: 0;
  width: 300px;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}
.menu ul
{
  position: relative;
}
.menu ul li
{
  list-style: none;
}
.menu ul li a
{
  text-decoration: none;
  font-size: 24px;
  color: #111;
}
.menu ul li a:hover
{
  color: #03a9f4; 
}

@media (max-width: 991px)
{
  .showcase,
  .showcase header
  {
    padding: 40px;
  }
  .text h2
  {
    font-size: 3em;
  }
  .text h3
  {
    font-size: 2em;
  }
}

</style>

</head>
<body>  
  <header>
      <h2 class="logo"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h2>
      <div class="toggle"></div>
    </header>
    <video src="http://localhost/ap/pro1/agb.webm" muted loop autoplay></video>
    <div class="overlay"></div>
    <div class="text">
      <h2>Agricultivate</h2> 
      <h3>Your Green Path to Informed Crop Choices</h3>
      <p>Welcome to Agricultivate: Your Crop Companion. Unearth personalized crop suggestions tailored to your land and aspirations. From seed to fruition, we guide you on a journey of bountiful harvests. Let's cultivate prosperity together</p>
      <form action="detailed_problem.php">
        <a href="detailed_problem.php">Submit Problem</a>
      </form>
    </div>
    <ul class="social">
      <li><a href="https://www.facebook.com/agriGoI/"><img src="https://i.ibb.co/x7P24fL/facebook.png" alt="Facebook"></a></li>
      <li><a href="https://twitter.com/AgriGoI"><img src="https://i.ibb.co/Wnxq2Nq/twitter.png" alt="Twitter"></a></li>
      <li><a href="https://www.instagram.com/agrigoi/?hl=en"><img src="https://i.ibb.co/ySwtH4B/instagram.png" alt="Instagram"></a></li>
    </ul>
  </section>
  <div class="menu">
    <ul>
      <li><a href="chat/search_doctor.php">schedule video call</a></li>
      <li><a href="patient_profile.php">Profile</a></li>
      <li><a href="patient_history.php">History</a></li>
    </ul>
  </div>
<script>
 const menuToggle = document.querySelector('.toggle');
 const showcase = document.querySelector('.showcase');

 menuToggle.addEventListener('click', () => {
   menuToggle.classList.toggle('active');
   showcase.classList.toggle('active');
 })
</script>
</body>
</html>
