<?php
require_once "../config/config.php";
require_once "../classes/session.php";

$session = new Session();
$session->require_auth();
$csrfToken = $session->set_csrf();
?>

<!doctype html>
<html>

<head>
	<title>Upload or capture picture</title>
</head>

<body>
	<a href="/index.php">HOME</a>

	<form id="uploadForm" method="POST" enctype="multipart/form-data">
		<input type="hidden" id="csrf_token" name="csrfToken" value="<?php echo $csrfToken; ?>">
		<input type="file" id="picture" name="picture" />
		<input type="button" value="upload" id="uploadBtn" />
		<fieldset>
			<legend>Select a second image:</legend>
			<div>
				<input type="radio" id="image1" name="secondImage" value="1">
				<label for="image1"><img src="image1.png" alt="Image 1" width="100" height="100"></label>
			</div>
			<div>
				<input type="radio" id="image2" name="secondImage" value="2">
				<label for="image2"><img src="image2.png" alt="Image 2" width="100" height="100"></label>
			</div>
			<div>
				<input type="radio" id="image3" name="secondImage" value="3">
				<label for="image3"><img src="image3.png" alt="Image 3" width="100" height="100"></label>
			</div>
		</fieldset>
	</form>
	<video id="webcam" width="640" height="480" autoplay></video>
	<button id="capture">Capture</button>
	<canvas id="canvas" width="640" height="480"></canvas>
	<div id="error-message"></div>

	<script>
		const video = document.getElementById('webcam');
		const canvas = document.getElementById('canvas');
		const ctx = canvas.getContext('2d');
		const captureButton = document.getElementById('capture');
		const uploadBtn = document.getElementById('uploadBtn');
		const fileInput = document.getElementById('picture');
		const secondImage = document.getElementsByName('secondImage');
		const errorMessage = document.getElementById('error-message');

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
						console.log("File successfully uploaded");
					} else {
						errorMessage.textContent = data.msg;
					}
				}).catch((error) => {
					console.error("Error uploading image:", error);
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
			ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
			canvas.toBlob(function(blob) {
				const formData = new FormData();
				formData.append('picture', blob, 'captured.jpg');
				uploadImage(formData);
			}, 'image/jpeg');
		});

		// For file input upload
		uploadBtn.addEventListener('click', function() {
			const formData = new FormData();
			formData.append('picture', fileInput.files[0]);
			uploadImage(formData);
		});
	</script>
</body>

</html>