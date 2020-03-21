<?php
session_start()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="stylo.css">
    <title>Hotel Booking</title>
</head>
<body>

<h1>Cape Town's best hotel-booking site</h1>

<main>

<form class="firstform" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
<h3>Search</h3>
<label for="">Name: 
<input type="text" name="firstname" required>
</label>
<label for="">Surname: 
<input type="text" name="secondname" required>
</label>
<label for="">Check-in: 
<input type="date" name="fromdate" required>
</label>
<label for="">Check-out: 
<input type="date" name="todate" required>
</label>
<label for="">Hotel: 
<select required name="hotel">
<option value="">-</option>
  <option value="The Backpack Hostel">The Backpack Hostel</option>
  <option value="Once in Cape Town">Once in Cape Town</option>
  <option value="Cape Town Lodge Hotel">Cape Town Lodge</option>
  <option value="The Westin Cape Town">The Westin Cape Town</option>
  <option value="Radisson Blu Hotel">Radisson Blu Hotel</option>
</select>
</label>
<input  class="button"type="submit" name="check" value="Calculate price">
</form>



<?php
require_once 'conn.php';



///////////////////////CLASSES/////////////////////////

class checkPrice{
        public $firstname, $secondname, $fromdate, $todate, $hotel, $daysBooked,$dailyRate, $value;
        
        function outputPrice(){
            echo "<div class=\"outputprice\"><h5>Please ensure your booking is correct and confirm</h3><br><br><div class=\"output-text\">Name: " . $this->firstname ."<br>" . "Surname: " . $this->secondname . "<br>" . "From: " . $this->fromdate . "<br>" . "To: " . $this->todate . "<br>" . "At: " . $this->hotel . "<br>". "Total days: ". $this->daysBooked . "<br>Daily rate: R" . $this->dailyRate . "<br>Price to be paid: R" . $this->value . "<br><br><form role=\"form\" action=\"index.php\" method=\"POST\">
            </div><button type=\"confirm\" class=\"button2\" value=\"confirm\" name=\"confirm\">Confirm my booking</button></form></div>";
        }
    }



class daysBooked{

    private $datetime1;
    private $datetime2;

    function dateTime($date1, $date2){
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval= $datetime1->diff($datetime2);
        $daysBooked= $interval->format('%R%a');
        return $daysBooked;
    }
}




class confirmBooking{
    function __construct($conn, $firstname, $secondname, $fromdate, $todate, $hotel){
        $this->conn = $conn;
        $this->firstname=$firstname;
        $this->secondname=$secondname;
        $this->fromdate=$fromdate;
        $this->todate=$todate;
        $this->hotel=$hotel;
    $stmt = $conn->prepare("INSERT INTO booking (firstname, secondname, fromdate, todate,hotel) VALUES (?,?,?,?,?)");
    echo mysqli_error($conn);
    $stmt->bind_param("sssss", $this->firstname, $this->secondname, $this->fromdate, $this->todate, $this->hotel);

    $stmt->execute();
    $conn->error_list;

    if(mysqli_error($conn)){
        
        // echo mysqli_error($conn);
        $sql = "SELECT * FROM booking WHERE secondname = '$this->secondname'";
        $result = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($result)){
            echo"<div class=\"duplicate\"><h5>A duplicate already exists:</h5><br>Name: " . $row["firstname"] . "<br>Surname: " . $row["secondname"] . "<br>From: " . $row["fromdate"] . "<br>To: " . $row["todate"] . "<br> Hotel: " . $row["hotel"] . "</div>" ;
        }
    }
    else{
    echo "<div class=\"booking-confirmed\"> Booking confirmed </div>";
    // mysqli_close($conn);
    }
}}















//if not yet created... create new table with the following properties
//also in order to detect dublicates the properties also include a constraint unique method native to mysql 
$sql ="CREATE TABLE booking(
    firstname VARCHAR(60),
    secondname VARCHAR(60),
    fromdate VARCHAR(16),
    todate VARCHAR(16),
    hotel VARCHAR(60),
    CONSTRAINT UC_Person UNIQUE (secondname))";

//passing the query to the established connection 
$conn->query($sql);



//redudant code only useful for testing purposes
// if(mysqli_query($conn, $sql)){
//     echo 'Table booking created succesffully.';
// }else{
//     echo 'error creating table:' . mysqli_error($conn);}


//translate the post data to session data
if(!empty($_POST['check'])){
    $_SESSION['firstname'] = $_POST['firstname'];
    $_SESSION['secondname'] = $_POST['secondname'];
    $_SESSION['fromdate'] = $_POST['fromdate'];
    $_SESSION['todate'] = $_POST['todate'];
    $_SESSION['hotel'] = $_POST['hotel'];


//amounts of days booked. Which is being passed to the class daysBooked
$daysBookedInstance = new daysBooked();
$daysBooked = $daysBookedInstance->dateTime($_SESSION['fromdate'], $_SESSION['todate']);
$_SESSION['daysBooked']=$daysBooked;



switch($_POST["hotel"]){
    case "Radisson Blu Hotel";
    $value = $daysBooked * 2710;
    $dailyRate= 2710;
    break;
    case "Cape Town Lodge Hotel";
    $value = $daysBooked * 676;
    $dailyRate=676;
    break;
    case "The Westin Cape Town";
    $value = $daysBooked * 1981;
    $dailyRate=1981;
    break;
    case "Once in Cape Town";
    $value = $daysBooked * 530;
    $dailyRate=530;
    break;
    case "The Backpack Hostel";
    $value = $daysBooked * 449;
    $dailyRate = 449;
    break;
}
$_SESSION['value'] = $value;
$_SESSION['dailyrate'] = $dailyRate;
};

if($_SESSION){
        $checkBooking = new checkPrice();
        $checkBooking->firstname=$_SESSION['firstname'];
        $checkBooking->secondname=$_SESSION['secondname'];
        $checkBooking->fromdate=$_SESSION['fromdate'];
        $checkBooking->todate=$_SESSION['todate'];
        $checkBooking->hotel= $_SESSION['hotel'];
        $checkBooking->daysBooked=$_SESSION['daysBooked'];
        $checkBooking->dailyRate=$_SESSION['dailyrate'];
        $checkBooking->value=$_SESSION['value'];
        
        $output = $checkBooking->outputPrice();
}





if(isset(($_POST['confirm']))){
        $confirmBooking = new confirmBooking($conn, $_SESSION['firstname'], $_SESSION['secondname'], $_SESSION['fromdate'], $_SESSION['todate'], $_SESSION['hotel']);
}

?>

<script>
$("button").click(function () {
                $(".outputprice").css("display","none");})


</script>
</main>
</body>
</html>
























































