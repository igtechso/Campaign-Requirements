<?php include("common/header.php"); 
session_start();
?>

<div class="main-content">
	<div class="login-page">
		<form class="login" method="post">
			<?php
			if (isset($_POST['login'])) {
				$email = $_POST['email'];
				$pass = md5($_POST['password']);

				$sql = "SELECT * FROM camp_register WHERE email='$email' AND pass='$pass'";
    			$result = mysqli_query($conn, $sql);

    			if (mysqli_num_rows($result) === 1) {
    				$row = mysqli_fetch_assoc($result);
    				$_SESSION['email'] = $row['email'];
	                $_SESSION['id'] = $row['id'];
	                header("Location: index.php");
	                exit();
    			}
    			else{
    				echo '<div class="msg error-msg">Invalid login details</div>';
    			}
			}
			?>
			<p class="title">Log in</p>
			<input type="text" name="email" placeholder="Username" autofocus required />
			<i class="fa fa-user"></i>
			<input type="password" name="password" placeholder="Password" required />
			<i class="fa fa-key"></i>
			<button type="submit" name="login"><i class="spinner"></i><span class="state">Log in</span></button>
		</form>
	</div>		
</div>

<?php include("common/footer.php"); ?>

