<?php
require_once "../config/config.php";
require_once "../classes/session.php";
require_once "../classes/database.php";

$session = new Session();
$session->require_auth();
$csrfToken = $session->set_csrf();

$db = Database::getInstance();
$conn = $db->getConnection();

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get the total number of pictures
$sql = "SELECT COUNT(*) AS total FROM pictures";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_pictures = $row['total'];

$total_pages = ceil($total_pictures / $items_per_page);

// Fetch pictures for the current page

$sql = "SELECT pictures.id AS picture_id,
				pictures.src AS picture_src,
				COUNT(DISTINCT likes.user_id) AS likes_count,
				users.username AS username,
				pictures.created_at AS created_at
				FROM pictures
				LEFT JOIN likes ON pictures.id = likes.picture_id
				JOIN users ON pictures.user_id = users.id
        GROUP BY pictures.id
        ORDER BY pictures.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$res = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>
	<title>HOME</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
	<script>
		function likePicture(pictureId) {
			const csrfToken = <?php echo json_encode($csrfToken); ?>;
			const formData = new FormData();
			formData.append('csrfToken', csrfToken);
			formData.append("pictureId", pictureId);
			fetch('like_picture.php', {
					method: 'POST',
					body: formData
				}).then(response => response.json())
				.then(data => {
					if (data.success) {
						let likesCount = document.getElementById(`likes-count-${pictureId}`).innerText;
						document.getElementById(`likes-count-${pictureId}`).innerText = "Likes: " + String(parseInt(likesCount.substring(7)) + 1);
					}
				}).catch((error) => {});
		}

		function addComment(pictureId) {
			const commentText = document.getElementById(`comment-input-${pictureId}`).value;
			const csrfToken = <?php echo json_encode($csrfToken); ?>;
			const formData = new FormData();
			formData.append('csrfToken', csrfToken);
			formData.append("pictureId", pictureId);
			formData.append("comment", commentText);
			fetch('add_comment.php', {
					method: 'POST',
					body: formData
				}).then(response => response.json())
				.then(data => {
					// Add the new comment to the UI
					const commentList = document.getElementById(`comment-list-${pictureId}`);
					const newComment = document.createElement('li');
					newComment.className = "list-group-item";
					const author = document.createElement('span');
					const date = document.createElement('span');
					const br = document.createElement('br');
					author.innerText = "<?php echo $session->get("uname") . ' - '; ?>";
					date.innerText = new Date().toISOString().replace('T', ' ').substring(0, 19);
					newComment.appendChild(author);

					newComment.appendChild(date);
					newComment.appendChild(br);
					const text = document.createElement('span');
					text.innerText = commentText
					newComment.appendChild(text)


					commentList.prepend(newComment);
				}).catch((error) => {});
		}

		function toggleComments(elementId) {
			const commentList = document.getElementById(elementId);
			if (commentList.style.display === "none") {
				commentList.style.display = "block";
			} else {
				commentList.style.display = "none";
			}
		}
	</script>

	<header class="bg-light">
		<div class="container">
			<nav class="navbar navbar-expand-lg navbar-light">
				<a class="navbar-brand" href="/index.php">Home</a>
				<div class="navbar-nav">
					<a class="nav-item nav-link" href="/upload.php">Upload</a>
					<a class="nav-item nav-link" href="/account.php">Account</a>
					<a class="nav-item nav-link" href="/logout.php">Logout</a>
				</div>
			</nav>
		</div>
	</header>

	<main class="container mt-5">
		<h2>List of uploaded images</h2>
		<div class="row">
			<?php
			while ($row = $res->fetch_assoc()) {
				$pictureId = $row['picture_id'];

				$sql_comments = "SELECT
													comments.comment AS comment,
													comments.created_at AS comment_date,
													users.username AS author
												FROM comments
												JOIN users ON comments.user_id = users.id
												WHERE comments.picture_id = ?
												ORDER BY comments.created_at DESC";
				$stmt_comments = $conn->prepare($sql_comments);
				$stmt_comments->bind_param("i", $pictureId);
				$stmt_comments->execute();
				$res_comments = $stmt_comments->get_result();
				echo "<div class='col-md-4 mb-4'>";
				echo "<div class='card'>";
				echo "<img src='image.php?src=" . $row["picture_src"] . "' alt='picture' class='card-img-top'>";
				echo "<div class='card-body'>";
				echo "<h5 class='card-title'>Uploaded by: " . htmlentities($row['username'], ENT_QUOTES, 'UTF-8') . "</h5>";
				echo "<p class='card-text'>Created at: " . htmlentities($row['created_at'], ENT_QUOTES, 'UTF-8') . "</p>";
				echo "<div id='likes-count-{$pictureId}'>Likes: " . htmlentities($row['likes_count'], ENT_QUOTES, 'UTF-8') . "</div>";
				echo "<button class='btn btn-primary mt-2' onclick='likePicture({$pictureId})'>Like</button>";
				echo "<button class='btn btn-secondary mt-2' onclick='toggleComments(\"comment-list-{$pictureId}\")'>Toggle Comments</button>";
				echo "<ul id='comment-list-{$pictureId}' class='list-group list-group-flush' style='display:none'>";
				while ($comment = $res_comments->fetch_assoc()) {
					echo "<li class='list-group-item'>";
					echo "<span class='comment-author'>" . htmlentities($comment['author'], ENT_QUOTES, 'UTF-8') . "</span> - ";
					echo "<span class='comment-date'>" . htmlentities($comment['comment_date'], ENT_QUOTES, 'UTF-8') . "</span><br>";
					echo htmlentities($comment['comment'], ENT_QUOTES, 'UTF-8');
					echo "</li>";
				}
				echo "</ul>";
				echo "<input type='text' id='comment-input-{$pictureId}' class='form-control mt-2' placeholder='Add a comment'>";
				echo "<button class='btn btn-success mt-2' onclick='addComment({$pictureId})'>Comment</button>";
				echo "</div>";
				echo "</div>";
				echo "</div>";
			}
			?>
		</div>
		<nav aria-label="Page navigation">
			<ul class="pagination pagination-lg">
				<?php
				for ($i = 1; $i <= $total_pages; $i++) {
					if ($i === $current_page) {
						echo "<li class='page-item active'><span class='page-link'>$i</span></li>";
					} else {
						echo "<li class='page-item'><a class='page-link' href='index.php?page=$i'>$i</a></li>";
					}
				}
				?>
			</ul>
		</nav>
	</main>

	<footer class="bg-light mt-5">
		<div class="container py-3">
			<p class="text-center mb-0">Copyright &copy;Antoine 2023, Camagru</p>
		</div>
	</footer>

	<input type="hidden" id="csrf_token" name="csrfToken" value="<?php echo $csrfToken; ?>" />

	<script>
		document.addEventListener("DOMContentLoaded", function() {
			let current_page = 1;
			const items_per_page = 10;
			let isLoading = false;

			function loadMore() {
				current_page++;
				fetch(`load_more.php?page=${current_page}`)
					.then(response => response.json())
					.then(data => {
						const container = document.querySelector('.row');
						data.forEach(item => {
							fetch(`load_comments.php?pictureId=${item.picture_id}`)
								.then(response => response.json())
								.then(data => {
									const comments = `
										${data.map(comment => `
											<li class='list-group-item'>
												<span class='comment-author'>${comment.author}</span> -
												<span class='comment-date'>${comment.comment_date}</span><br>
												${comment.comment}
											</li>
										`).join('')}
									`;
									const newElement = document.createElement('div');
									newElement.className = 'col-md-4 mb-4';
									newElement.innerHTML = `
									<div class='card'>
										<img src='image.php?src=${item.picture_src}' alt='picture' class='card-img-top'>
										<div class='card-body'>
											<h5 class='card-title'>Uploaded by: ${item.username}</h5>
											<p class='card-text'>Created at: ${item.created_at}</p>
											<div id='likes-count-${item.picture_id}'>Likes: ${item.likes_count}</div>
											<button class='btn btn-primary mt-2' onclick='likePicture(${item.picture_id})'>Like</button>
											<button class='btn btn-secondary mt-2' onclick='toggleComments(\"comment-list-${item.picture_id}\")'>Toggle Comments</button>
											<ul id='comment-list-${item.picture_id}' class='list-group list-group-flush' style='display:none'>
															${comments}
											</ul>
											<input type='text' id='comment-input-${item.picture_id}' class='form-control mt-2' placeholder='Add a comment'>
											<button class='btn btn-success mt-2' onclick='addComment(${item.picture_id})'>Comment</button>
										</div>
									</div>
								`;

									container.appendChild(newElement);
									setTimeout(() => {
										isLoading = false;
									}, 500);
								});
						});
					});
			}

			window.addEventListener('scroll', function() {
				if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 10 && !isLoading) {
					isLoading = true;
					loadMore();
				}
			});
		});
	</script>
</body>

</html>