<?php
$host = "mysql";
$user = "root";
$pass = "";
$db = "mivento";

$file = $_FILES['file']["tmp_name"];

$hatalar = array();
$varmi = false;

if (($handle = fopen($file, "r")) !== FALSE)
{
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE)
    {
            try {
                $db = new PDO("mysql:host=localhost;dbname=mivento", "root", "");
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $telNo = $data[4];
                $telUzunluk = strlen($telNo);
                $pattern = '/5[0,3,4,5,6][0-9]\d\d\d\d\d\d\d$/';
                $eslesme = preg_match($pattern, $telNo);

                 if (!filter_var($data[2], FILTER_VALIDATE_EMAIL))
                    $hatalar[] = $data[0] . ' isimli kullanıcının mail adresi geçersiz';
                 else if ($eslesme != 1 and $telUzunluk != 10)
                    $hatalar[] = $data[0] . ' isimli kullanıcının telefonu numarası hatalı';
                 else
                 {
                    $sorgu = $db->prepare("SELECT * FROM task WHERE email=? or phone=? or employee_id=?");
                    $sorgu->execute(array($data[2], $data[4], $data[3]));

                    if ($sorgu->rowCount())
                    {
                        $hatalar[] = $data[0] . ' isimli kullanıcının mail adresi, telefon numarası veya çalışan kimliği daha önce girilmiş';
                    }
                    else
                    {
                        $sorgu = $db->prepare("INSERT INTO task(name, surname, email,employee_id,phone,point) VALUES(?, ?, ?,?,?,?)");
                        $sorgu->execute($data);
                    }
                }
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
    }
    fclose($handle);

    echo json_encode($hatalar);
}

?>

