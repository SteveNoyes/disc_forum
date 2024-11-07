<?php
// Database connection
$host = 'localhost';
$dbname = 'discussion_forum';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}

// Handle new post submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["username"]) && !empty($_POST["content"])) {
        // New post
        $username = htmlspecialchars($_POST["username"]);
        $content = htmlspecialchars($_POST["content"]);
        $stmt = $pdo->prepare("INSERT INTO posts (username, content) VALUES (:username, :content)");
        $stmt->execute(['username' => $username, 'content' => $content]);
        header("Location: forum.php");
        exit;
    } elseif (isset($_POST['like_post'])) {
        // Like for a post
        $postId = (int)$_POST['post_id'];
        $stmt = $pdo->prepare("UPDATE posts SET likes = likes + 1 WHERE id = :id");
        $stmt->execute(['id' => $postId]);
        header("Location: forum.php");
        exit;
    } elseif (isset($_POST['reply']) && !empty($_POST['reply_content']) && !empty($_POST['reply_username'])) {
        // New reply
        $postId = (int)$_POST['post_id'];
        $replyUsername = htmlspecialchars($_POST['reply_username']);
        $replyContent = htmlspecialchars($_POST['reply_content']);
        $stmt = $pdo->prepare("INSERT INTO replies (post_id, username, content) VALUES (:post_id, :username, :content)");
        $stmt->execute(['post_id' => $postId, 'username' => $replyUsername, 'content' => $replyContent]);
        header("Location: forum.php");
        exit;
    } elseif (isset($_POST['like_reply'])) {
        // Like for a reply
        $replyId = (int)$_POST['reply_id'];
        $stmt = $pdo->prepare("UPDATE replies SET likes = likes + 1 WHERE id = :id");
        $stmt->execute(['id' => $replyId]);
        header("Location: forum.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discussion Forum</title>
</head>
<body>
    <h1>Discussion Forum</h1>

    <!-- Form to submit new post -->
    <form method="post" action="">
        <input type="text" name="username" placeholder="Your name" required><br><br>
        <textarea name="content" placeholder="Write your post here" required></textarea><br><br>
        <button type="submit">Post</button>
    </form>

    <hr>

    <!-- Display posts and replies -->
    <h2>Posts:</h2>
    <?php
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY post_date DESC");
    while ($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<p><strong>" . htmlspecialchars($post['username']) . "</strong> (" . $post['post_date'] . ")<br>";
        echo htmlspecialchars($post['content']) . "</p>";

        // Like button for post
        echo "<form method='post' action=''>
                <input type='hidden' name='post_id' value='" . $post['id'] . "'>
                <button type='submit' name='like_post'>Like (" . $post['likes'] . ")</button>
              </form>";

        // Display replies
        echo "<div style='margin-left:20px;'>";
        $replyStmt = $pdo->prepare("SELECT * FROM replies WHERE post_id = :post_id ORDER BY reply_date ASC");
        $replyStmt->execute(['post_id' => $post['id']]);
        while ($reply = $replyStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<p><strong>" . htmlspecialchars($reply['username']) . "</strong> (" . $reply['reply_date'] . ")<br>";
            echo htmlspecialchars($reply['content']) . "</p>";

            // Like button for reply
            echo "<form method='post' action='' style='margin-left:20px;'>
                    <input type='hidden' name='reply_id' value='" . $reply['id'] . "'>
                    <button type='submit' name='like_reply'>Like (" . $reply['likes'] . ")</button>
                  </form>";
        }
        echo "</div>";

        // Reply form
        echo "<form method='post' action='' style='margin-left:20px;'>
                <input type='hidden' name='post_id' value='" . $post['id'] . "'>
                <input type='text' name='reply_username' placeholder='Your name' required><br><br>
                <textarea name='reply_content' placeholder='Write your reply here' required></textarea><br><br>
                <button type='submit' name='reply'>Reply</button>
              </form>";
        
        echo "<hr>";
    }
    ?>
</body>
</html>
