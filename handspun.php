<?php
//Create arrays.
$fiber=array(
     "Blue Faced Leicester",
     "Merino",
     "Alpaca",
     "Llama",
     "Yak",
     "Other"
  );
   
$weight=array(
     "Lace",
     "Sock",
     "Aran",
     "Worsted",
     "Bulky",
     "Super-Bulky"
);
 
$ply=array(
     "Singles",
     "2 ply",
     "3 ply",
     "n ply",
     "Cabled"
);

$colorway=array(
     "Purple",
     "Green",
     "Blue",
     "Gray"
);
 
class Select{

  private $name;
  private $value;  
   

  public function setName($name){
     $this->name = $name;
  }
  public function getName(){
     return $this->name;
  }

  public function setValue($value){
     if (!is_array($value)){
        die ("Error: not an array.");
     }
     $this->value = $value;
   }
   
  public function getValue(){
     return $this->value;
  }
   
  private function makeOptions($value){
     foreach($value as $v){
        echo "<option value=\"$v\">" .ucfirst($v). "</option>\n";
      }
  }
   
  public function makeSelect(){
     echo "<select name=\"" .$this->getName(). "\">\n";
     $this->makeOptions($this->getValue());
     echo "</select>" ;
  }
}
 
?>
 
 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml"  xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />
<title>Handspun Inventory Tracking</title>
</head>
 
<body>
<h2>Yarn<br /></h2>
 
<?php
//If form not submitted, display form.
  if(!isset($_POST['submit'])){
?>
   
<form method="post" action="handspun.php">
<p>Name:<br />
<input type="text" name="name" size="60" />  </p>


<p><strong>Handspun</strong></p>

<p>Fiber composition: 
<?php
$new_fiber = new Select();
$new_fiber->setName('fiber');
$new_fiber->setValue($fiber);
$new_fiber->makeSelect();
unset($new_fiber);
 
echo "</p>\n<p>Weight: ";
$new_weight = new Select();
$new_weight->setName('weight');
$new_weight->setValue($weight);
$new_weight->makeSelect();
unset($weight);
 
echo "</p>\n<p>Ply: ";
$new_ply = new Select();
$new_ply->setName('ply');
$new_ply->setValue($ply);
$new_ply->makeSelect();
unset($ply);

echo "</p>\n<p>Primary colorway: ";
$new_colorway = new Select();
$new_colorway->setName('colorway');
$new_colorway->setValue($colorway);
$new_colorway->makeSelect();
unset($new_colorway);
 
?>

</p>
 
<p />
<input type="submit" name="submit" value="Go" />
 
</form>
 
<?php
  //If form submitted, process input
  }else{
    //@TODO send data to database here
    //retrieve  responses
    $name=$_POST['name'];
    $fiber=$_POST['fiber'];
    $weight=$_POST['weight'];
    $ply=$_POST['ply'];
    $colorway=$_POST['colorway'];
 
    //display responses
    echo "<p>The following data has been saved for $name: </p>\n";
    echo "<ul>\n<li>$fiber</li>\n";
    echo "<li>$weight</li>\n";
    echo "<li>$ply</li>\n";
    echo "<li>$colorway</li>\n";

}
?>
 
</body>
</html>