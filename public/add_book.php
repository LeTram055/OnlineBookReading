<?php
require_once __DIR__ . '/../src/connect.php';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_book = isset($_POST['id_book']) ? $_POST['id_book'] : '';
    $name_book = isset($_POST['name_book']) ? $_POST['name_book'] : '';
    $author = isset($_POST['author']) ? $_POST['author'] : '';
    $describe = isset($_POST['describe']) ? $_POST['describe'] : '';
    $id_genre = isset($_POST['genre']) ? $_POST['genre'] : '';
    
    $sql_genres = "SELECT name_genre FROM genre WHERE id_genre = ?";
    $stmt_genres = $pdo->prepare($sql_genres);
    $stmt_genres->execute([$id_genre]);
    $row_genre = $stmt_genres->fetch(PDO::FETCH_ASSOC);
    $name_genre = $row_genre['name_genre'];

    //Xử lý ảnh
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_folder = 'images/' . $name_genre . '/';
        $image_path = $image_folder . $image_name;

        // Di chuyển hình ảnh vào thư mục lưu trữ
        if (move_uploaded_file($image_tmp, $image_path)) {
            $image = $image_path; // Gắn đường dẫn hình ảnh vào biến
        } else {
            $error_message = "Không thể tải lên hình sản phẩm.";
        }
    }

    //Xử lí file
    $file = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_folder = 'files/' . $name_genre . '/';
        $file_path = $file_folder . $file_name;

        // Di chuyển file vào thư mục lưu trữ
        if (move_uploaded_file($file_tmp, $file_path)) {
            $file = $file_path; // Gắn đường dẫn file vào biến
        } else {
            $error_message = "Không thể tải lên file sản phẩm.";
        }
    }


    $sql_check = "SELECT COUNT(*) AS count FROM book WHERE id_book = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$id_book]);
    $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] > 0) {
        echo '<script>alert("Mã sách đã tồn tại. Vui lòng chọn mã sách khác.");</script>';
       
    } else {

        // Thực hiện chèn loại vào cơ sở dữ liệu
        $sql = "INSERT INTO book (id_book, name_book, author, describe_book, image_book, file_book, id_genre) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id_book, $name_book, $author, $describe, $image, $file, $id_genre]);

        // Kiểm tra kết quả cập nhật
        if ($result) {
            // Thêm loại thành công
            echo "<script>
                        alert ('Thêm sách thành công!');
                        window.location.href = 'manage_book.php';
                    </script>";
            
        } else {
            // Thêm loại không thành công
            echo '<script>alert("Thêm sách không thành công. Vui lòng kiểm tra lại thông tin.");</script>';
        }
    }
}

if ($error_message) {
    include __DIR__ . '/../src/partials/show_error.php';
}

include_once __DIR__. '/../src/partials/header_ad.php'
?>

<div class="container-fluid flex-grow-1">
    <div class="row">
        <div class="col-2">
            <?php include_once __DIR__. '/../src/partials/slidebar_ad.php' ?>

        </div>

        <div class="col-10">

            <div class="row mt-3">
                <div class="col">
                    <button class="btn btn-light" id="goBackBtn"><i class="fa-solid fa-chevron-left"></i></button>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-6 border-form">
                    <h2 class="text-center">THÊM SÁCH</h2>
                    <form method="post" enctype="multipart/form-data">

                        <input type="hidden" name="id_book" value="<?= $id_book ?>">

                        <!-- Mã loại -->
                        <div class="form-group m-1">
                            <label for="id_book">Mã sách:</label>
                            <input type="text" name="id_book" class="form-control" maxlen="10" id="id_book"
                                placeholder="Nhập mã sách"
                                value="<?= isset($_POST['id_book']) ? html_escape($_POST['id_book']) : '' ?>"
                                required />
                        </div>

                        <!-- Tên loại -->
                        <div class="form-group m-1">
                            <label for="name_book">Tên sách:</label>
                            <input type="text" name="name_book" class="form-control" maxlen="100" id="name_book"
                                placeholder="Nhập tên sách"
                                value="<?= isset($_POST['name_book']) ? html_escape($_POST['name_book']) : '' ?>"
                                required />
                        </div>

                        <!-- Tác giả -->
                        <div class="form-group m-1">
                            <label for="author">Tác giả:</label>
                            <input type="text" name="author" class="form-control" maxlen="50" id="author"
                                placeholder="Nhập tên tác giả"
                                value="<?= isset($_POST['author']) ? html_escape($_POST['author']) : '' ?>" />
                        </div>

                        <!-- Mô tả -->
                        <div class="form-group m-1">
                            <label for="describe">Mô tả:</label>
                            <input type="text" name="describe" class="form-control" maxlen="50" id="describe"
                                placeholder="Nhập tên mô tả"
                                value="<?= isset($_POST['describe']) ? html_escape($_POST['describe']) : '' ?>" />
                        </div>

                        <!-- Ảnh bìa sách -->
                        <div class="form-group m-1 my-3">
                            <label for="image">Ảnh bìa sách: </label>
                            <image id="book-preview" style="display: none;" alt="book" width="40px" height="60px">
                                <input type="file" name="image" id="image" class="form-control-file" id="image"
                                    required />
                        </div>

                        <!-- File -->
                        <div class="form-group m-1 my-3">
                            <label for="file">File pdf: </label>
                            <input type="file" name="file" class="form-control-file" id="file" accept=".pdf" required>
                        </div>

                        <!-- Tên thể loại -->
                        <div class="form-group m-1">
                            <label for="genre">Tên thể loại: </label>
                            <select name="genre" id="genre" class="form-control" required>
                                <option value="">Chọn thể loại</option>
                                <?php
                                $sql_genres = "SELECT id_genre, name_genre FROM genre";
                                $stmt_genres = $pdo->prepare($sql_genres);
                                $stmt_genres->execute();
                                while ($row_genre = $stmt_genres->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $row_genre['id_genre'] . "'>" . $row_genre['name_genre'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Submit -->
                        <div class="text-center m-3">

                            <button type="submit" name="submit" class="btn btn-primary">Thêm</button>
                        </div>
                    </form>


                </div>

            </div>


        </div>

    </div>


</div>

<?php
include_once __DIR__. '/../src/partials/footer_ad.php'
?>