<?php
/**
 * Plugin Name: Ajax Security Lesson
 */


add_action( 'wp_ajax_meta_preview', 'meta_preview' );
add_action( 'wp_ajax_nopriv_meta_preview', 'meta_preview' );
function meta_preview(){

  if(isset($_POST["link"]))
  {  
     $main_url=$_POST["link"];
     @$str = file_get_contents($main_url);
  
  
     // This Code Block is used to extract title
     if(strlen($str)>0)
     {
       $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
       preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title);
     }
    
  
     // This Code block is used to extract description 
     $b =$main_url;
     @$url = parse_url( $b ) ;
  
  
    //  $youtubeUrl = parse_url($b, PHP_URL_QUERY);
    //  parse_str($youtubeUrl);
     
  
     @$tags = get_meta_tags( $main_url );
  
     // This Code Block is used to extract og:image which facebook extracts from webpage it is also considered 
     // the default image of the webpage
     $d = new DomDocument();
     @$d->loadHTML($str);
     $xp = new domxpath($d);
     foreach ($xp->query("//meta[@property='og:image']") as $el){
       $l2=parse_url($el->getAttribute("content"));
       if($l2['scheme']){
        $img[]=$el->getAttribute("content");
     // print_r($img2);
        }
       else{
  
       }
     }
     $imggs = $d->getElementsByTagName('img');
     $imgg = $imggs->item(0); 
     // $img = @$url . $imgg->getAttribute('src');
     // $img = @$url . $imgg->getAttribute('src');
  }   
  ?>
     <a href="<?php echo $main_url;?>" style="text-decoration: none;"  target="_blank">
     
     <?php
       if(!empty($img)) {
          echo "<img  style='max-height:100%; max-width:100%;' src='".$img[0]."'>";
        }  else if(strlen($imgg->getAttribute('src'))>30) {
          echo "<img src='".$imgg->getAttribute('src')."' class='container-resposne'>" ;
        }
         echo "<H2 class='title' >".$title[1]."</H2>";
  
        // echo var_dump($_SERVER);
        // echo $_SERVER['HTTP_REFERER'];
        // echo $_SERVER['HTTP_ORIGIN'];
        // echo $_SERVER['REQUEST_URI'];
        // echo $_SERVER['QUERY_STRING'];
     ?>
     </a>
     <?php
    }



// Подключение JS и CSS
add_action( 'wp_enqueue_scripts', 'my_assets' );
function my_assets() {
	// wp_enqueue_script( 'sweetalert', plugins_url( 'assets/sweetalert.js', __FILE__ ), array( 'jquery' ) );
	// wp_enqueue_style( 'sweetalert', plugins_url( 'assets/sweetalert.css', __FILE__ ) );
	
	wp_enqueue_script( 'custom', plugins_url( 'assets/custom.js', __FILE__ ) );
	
	wp_localize_script( 'custom', 'myPlugin', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
	) );
}