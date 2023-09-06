<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();
$csrfToken = $session->set_csrf();
$userId = $session->get("userId");
$db = Database::getInstance();
$conn = $db->getConnection();
$sql = "SELECT src, id FROM pictures WHERE user_id = ? ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Upload or Capture Picture</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
	<style>
		#canvas {
			display: none;
			/* Hide the canvas */
		}

		#error-message {
			color: red;
			/* Make the error message red */
		}

		.image-selection {
			display: inline-block;
			margin: 10px;
		}

		.image-selection img {
			border: 3px solid transparent;
		}

		.image-selection input[type="radio"]:checked+label img {
			border-color: blue;
			/* Blue border around the selected image */
		}

		#cameraContainer {
			background-color: black;
			display: flex;
			justify-content: center;
			align-items: center;
			position: relative;
		}

		.overlay-image {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 2;
		}
	</style>
</head>

<body>

	<header class="bg-light">
		<div class="container">
			<nav class="navbar navbar-expand-lg navbar-light">
				<a class="navbar-brand" href="/index.php">Home</a>
				<div class="navbar-nav">
					<a class="nav-item nav-link active" href="/upload.php">Upload</a>
					<a class="nav-item nav-link" href="/account.php">Account</a>
					<a class="nav-item nav-link" href="/logout.php">Logout</a>
				</div>
			</nav>
		</div>
	</header>

	<main class="container mt-5">
		<div class="row">
			<div class="col-lg-8">
				<div id="error-message"></div>
				<div class="bg-light p-3 rounded">
					<fieldset>
						<legend>Select a second image:</legend>
						<div class="image-selection">
							<input type="radio" id="image1" name="secondImage" value="1">
							<label for="image1"><img src="image1.png" alt="Image 1" width="100" height="100"></label>
						</div>
						<div class="image-selection">
							<input type="radio" id="image2" name="secondImage" value="2">
							<label for="image2"><img src="image2.png" alt="Image 2" width="100" height="100"></label>
						</div>
						<div class="image-selection">
							<input type="radio" id="image3" name="secondImage" value="3">
							<label for="image3"><img src="image3.png" alt="Image 3" width="100" height="100"></label>
						</div>
					</fieldset>
				</div>
				<div id="cameraContainer" class="w-100 mb-3">
					<video id="webcam" class="w-100" autoplay></video>
					<img id="overlayImage" src="" alt="" class="overlay-image">
				</div>
				<button id="capture" class="btn btn-primary mb-3" disabled>Capture</button>
				<canvas id="canvas" class="w-100 mb-3"></canvas>
				<input type="hidden" id="csrf_token" name="csrfToken" value="<?php echo $csrfToken; ?>">
				<input type="file" id="picture" name="picture" class="form-control mb-3">
				<button id="uploadBtn" type="submit" class="btn btn-success" disabled>Upload</button>
			</div>
			<div class="col-lg-4">
				<div id="sidebar" class="bg-light p-3 rounded">
					<h3>Previous Images</h3>
					<?php while ($row = $res->fetch_assoc()) : ?>
						<div class='mb-3' style="position: relative;" id="img-<?php echo $row['id']; ?>">
							<img src="<?php echo 'image.php?src=' . $row['src']; ?>" alt='picture' class='img-thumbnail'>
							<i class="fas fa-trash position-absolute" style="bottom: 10px; right: 10px; cursor:pointer; font-size: 38px; padding: 7px; background-color: white; color: red" onclick="deleteImage(<?php echo $row['id'] . ', \'' . $row['src'] . '\''; ?>)"></i>
						</div>
					<?php endwhile; ?>
				</div>
			</div>
		</div>
	</main>

	<footer class="bg-light mt-5">
		<div class="container py-3">
			<p class="text-center mb-0">Copyright &copy;Antoine 2023, Camagru</p>
		</div>
	</footer>

	<script>
		const video = document.getElementById('webcam');
		const canvas = document.getElementById('canvas');
		const ctx = canvas.getContext('2d');
		const captureButton = document.getElementById('capture');
		const uploadBtn = document.getElementById('uploadBtn');
		const fileInput = document.getElementById('picture');
		const secondImage = document.getElementsByName('secondImage');
		const errorMessage = document.getElementById('error-message');
		const cameraContainer = document.getElementById('cameraContainer');
		const radioButtons = document.getElementsByName('secondImage');
		const overlayImage = document.getElementById('overlayImage');
		overlayImage.hidden = true;

		cameraContainer.style.height = errorMessage.offsetWidth + "px";
		window.addEventListener("resize", function() {
			cameraContainer.style.height = errorMessage.offsetWidth + "px";
		});

		for (let i = 0; i < radioButtons.length; i++) {
			radioButtons[i].addEventListener('change', function() {
				overlayImage.hidden = false;
				overlayImage.src = `image${this.value}.png`;
				const btns = document.getElementsByClassName("btn");
				// enable all btn in btns
				for (let i = 0; i < btns.length; i++) {
					btns[i].removeAttribute('disabled');
				}
			});
		}

		function deleteImage(id, src) {
			const formData = new FormData();
			formData.append('csrfToken', document.getElementById('csrf_token').value);
			formData.append("id", id);
			formData.append("src", src)
			fetch('delete_picture.php', {
					method: 'POST',
					body: formData,
				}).then(response => response.json())
				.then(data => {
					if (data.success) {
						const img = document.getElementById("img-" + id);
						img.remove()
					} else {
						errorMessage.textContent = data.msg;
					}
				}).catch((error) => {
					errorMessage.textContent = "Something went wrong, try again"
				});

		}

		function uploadImage(formData) {
			formData.append('csrfToken', document.getElementById('csrf_token').value);

			let secondImageSelected = false;
			for (let i = 0; i < secondImage.length; i++) {
				if (secondImage[i].checked) {
					formData.append('secondImage', secondImage[i].value);
					secondImageSelected = true;
					break;
				}
			}

			// If no second image is selected, display an error message
			if (!secondImageSelected) {
				errorMessage.textContent = "Please select a second image.";
				return;
			}

			fetch('upload_data.php', {
					method: 'POST',
					body: formData,
				}).then(response => response.json())
				.then(data => {
					if (data.success) {
						const sidebar = document.getElementById("sidebar");

						const newDiv = document.createElement("div");
						newDiv.className = 'mb-3';

						const newImg = document.createElement("img");
						newImg.src = data.img;
						newImg.alt = 'picture';
						newImg.className = 'img-thumbnail';

						newDiv.appendChild(newImg);

						// Insert the new div at the beginning of the sidebar
						sidebar.insertBefore(newDiv, sidebar.firstChild.nextSibling.nextSibling);
					} else {
						errorMessage.textContent = data.msg;
					}
				}).catch((error) => {
					errorMessage.textContent = "Something went wrong, try again"
				});
		}

		// For webcam capture
		navigator.mediaDevices.getUserMedia({
				video: true
			})
			.then((stream) => {
				video.srcObject = stream;
			});

		captureButton.addEventListener('click', function() {
			canvas.width = video.videoWidth;
			canvas.height = video.videoHeight;
			ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
			canvas.toBlob(function(blob) {
				const formData = new FormData();
				formData.append('picture', blob, 'captured.jpg');
				uploadImage(formData);
			}, 'image/jpeg');
		});

		// For file input upload
		uploadBtn.addEventListener('click', function(e) {
			const formData = new FormData();
			formData.append('picture', fileInput.files[0]);
			uploadImage(formData);
		});
	</script>
</body>

</html>