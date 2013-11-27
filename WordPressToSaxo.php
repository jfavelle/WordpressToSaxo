<?php
/*
Plugin Name: WordPressToSaxo
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A way to get content from WP to Saxo
Version: 1.2
Author: Jesse Favelle
*/

//Action that adds custom meta checkbox
function cdMetaBoxAdd()
{
            add_meta_box('meta_id', 'Story Details', 'cdMetaboxCb', 'post','normal', 'high');
            add_meta_box('picture', 'Picture Upload', 'cdMetaboxPic','post', 'normal', 'high');
}

function cdMetaboxPic()
{
            ?>
            <table>
            <tr>
                        <th><label for="file">Photos:</label></th>
                        <th><input type='file' name='file' id='file' multiple='yes' /></th>
            </tr>
                        
            </table>
            
            
            
            <?php
}

function cdMetaboxCb($post)
{
           
            
            // Create connection to Oracle
            $conn = oci_connect('saxo', 'pica55o', 'saxotst.ca.canwest.com/SAXOTST');
            if (!$conn) {
               $m = oci_error();
               echo $m['message'], '\n';
               exit;
            }
            else {
                  
               global $user_login;
               get_currentuserinfo();
                        
               ////get username from saxo database
               $sql6 = "select ID, NAME, INITIALS from saxo.sx_userdata where name = '" . $user_login . "'";
               $stid5 = oci_parse($conn, $sql6);
               $result = oci_execute($stid5);
                        
                        
                        while ($row7 = oci_fetch_array($stid5, OCI_BOTH))
                        {                            
                                    $userId= $row7['ID'];
                                    
                                    $userLogin= $row7['INITIALS'];
                        }
                        
               //checks if story is in saxo
               $customCheck = get_post_meta($post->ID, 'id', true);        
               if ($customCheck)
               {
                        
                        //gets previously selected category, product from saxo database
                        $sql5 = "select CATEGORY, PRODUCT, ACCESSLEVEL, PUBLICATION from saxo.sx_article  where id = " . $customCheck;
                        $stid4 = oci_parse($conn, $sql5);
                        $result = oci_execute($stid4);
                                     
                        while ($row = oci_fetch_array($stid4, OCI_BOTH))
                        {
                          $categoryC = $row['CATEGORY'];
                          $productC = $row['PRODUCT'];        
                          $publicationC = $row['PUBLICATION'];
                          $accessLevelC = $row['ACCESSLEVEL'];
                        }
                        
                        $sql7 = "select releasedatetime from saxo.sx_publication where id = $publicationC ";
                        $stid7 = oci_parse($conn, $sql7);
                        $result = oci_execute($stid7);
                
                        while ($row7 = oci_fetch_array($stid7, OCI_BOTH))
                        {
                          $release = $row7['RELEASEDATETIME'];
                          $release = date_create($release);
                          $release =  date_format($release, 'Y-m-d');
                        }
                       
                        
                        //locks story in saxo 
                        //$lockxml = "<sax:story xmlns:sax='http://www.saxotech.com/editorial'></sax:story>";
                        //$putData = fopen('php://temp/maxmemory:256000', 'w');  
                        //if (!$putData) {  
                        //             die('could not open temp memory data');  
                        //}
                        //
                        //fwrite($putData, $lockxml);  
                        //fseek($putData, 0);  
                        //$curl4 = curl_init();
                        //curl_setopt($curl4, CURLOPT_URL, "http://" . $userLogin . ":saxo@10.81.2.77:8080/ews/products/" . $productC . "/stories/" . $customCheck . "/lock" );
                        //curl_setopt($curl4, CURLOPT_PUT, 1);
                        //curl_setopt($curl4, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
                        //curl_setopt($curl4, CURLOPT_INFILE, $putData);
                        //curl_setopt($curl4, CURLOPT_INFILESIZE, strlen($lockxml));
                        //$curl_response = curl_exec($curl4);   
                        //curl_close($curl4);
                       
               }  
                                              
               //get categorys to display
               $sql = "select ID, NAME from saxo.sx_category where ID in ( 610343469, 877465922) ";
               $stid = oci_parse($conn, $sql);
               $result = oci_execute($stid);
               $dropdown = "<select id='postCategory' name='postCategory'>";
              
               while ($row = oci_fetch_array($stid, OCI_BOTH))
               {
                       
                      $dropdown .= "<option value='" . $row['ID'] . "'";
                      if($categoryC == $row['ID'])
                        {
                         $dropdown .= " selected='selected'>";
                        }
                      else
                        {
                         $dropdown .= ">";
                        }
                        $dropdown .= $rowID = $row['NAME'] . "</option>";               

               } 
              
               $dropdown .="</select>";
               
               //get products to display based on userId
               $sql2 = "select p.ID, p.NAME
                        from saxo.sx_product p, saxo.sx_product_productuser ppu, saxo.sx_productuser pu 
                        where p.productuserlist = ppu.id and ppu.relid = pu.id and pu.userdata =" . $userId;
               $stid2 = oci_parse($conn, $sql2);
               $result = oci_execute($stid2);
               $dropdown2 = "<select id='postArea' name='postArea'>";
               while ($row = oci_fetch_array($stid2, OCI_BOTH))
               {                      
                        $dropdown2 .= "<option value='" . $row['ID'] . "'" ;
                        if ($productC == $row['ID'])
                        {
                          $dropdown2 .= " selected='selected'>";
                        }
                        else
                        {
                          $dropdown2 .= ">";
                        }
                        $dropdown2 .= $rowID = $row['NAME'] . "</option>";
                                    
               }
               $dropdown2 .= "</select>";
               
               
                
               
               //get workflows to display
               $sql3 = "select ID, NAME from saxo.sx_accesslevel";
               $stid3 = oci_parse($conn, $sql3);
               $result = oci_execute($stid3);
               $dropdown3 = "<select id='postWorkflow' name='postWorkflow'>";
               while ($row = oci_fetch_array($stid3, OCI_BOTH))
               {
                        $dropdown3 .= "<option value='" . $row['ID'] . "'" ;
                        if ($accessLevelC == $row['ID'])
                        {
                                    $dropdown3 .= " selected='selected'>";
                        }
                        else
                        {
                                    $dropdown3 .= ">";
                        }
                        $dropdown3 .= $rowID = $row['NAME'] . "</option>";      
               }
               $dropdown3 .= "</select>";                
                
            }
            // Close the Oracle connection
            oci_close($conn);
            $options = get_option('options');
       
        ?>
        
        <!-- Story Package HTML-->
        <!--<label for='my_meta_box_check'>Create Story Package</label><br />-->
        <!--<input type="checkbox" id="storyPackCheck" name="storyPackCheck" value="1" <?php checked(1, get_option('automatic'));?>  />  <br />-->
        <!--<label for="name">Name:</label><input type="text" id="packageName" name="packageName"><br />
        <label for="summary">Summary:</label><input type="textbox" id="packageSummary" name="packageSummary" /><br />
        <label for="startDate">Start Date:</label><input name="startDate" type="date" id="startDate" ><br />
        <label for="endDate">End Date:</label><input name="endDate" type="date" id="endDate" ><br />-->
        
       <!-- display form with custom value-->
        <table>
        <tr>
            <th><a target="blank" href="http://wpgmerweb01.ca.canwest.com/">Merlin Web</a></th>
        </tr>
        <tr>
            <th><a target="blank" href="http://10.81.2.77:8080/saxoportal/saxoportal/SaxoPortal.html?locale=en">Reporter Client</a></th>
        </tr>
        <tr>
            <th><label for="deadlineDate">Deadline:</label></th>
            <td><input name="deadlineDate" id="deadlineDate" type="date"<?php
            //display date in calender
            if (isset($release))
            {
                        echo 'value="' . $release . '"/>';
            }
            else
            {
                        $tomorrow = date('Y-m-d', time() + 86400);
                      echo 'value="' . $tomorrow  . '"/>';  
            }
            ?></td>
        </tr>
        <tr>
            <th><label for="category">Category:</label></th>
            <td><?=$dropdown;?></td>
        </tr>
        <tr>
            <th><label for="area">Product:</label></th>
            <td><?=$dropdown2;?></td>
        </tr>
        <tr>
            <th><label for="workflow">Workflow:</label></th>
            <td><?=$dropdown3;?></td>
        </tr>
        </table>
<?php
            
}

//Action that posts story from wordpress to Saxo
function postStory($post_ID, $post)
{
            //if (get_post_status($ID) == 'draft'){
            //            
            //}
            
            //get login that user logged in with from WP
            global $user_login, $user_password;
            get_currentuserinfo();
          
             //Create connection to Oracle
            $conn = oci_connect('saxo', 'pica55o', 'saxotst.ca.canwest.com/SAXOTST');
            if (!$conn) {
               $m = oci_error();
               trigger_error(htmlentities($m['message'], ENT_QUOTES), E_USER_ERROR);
            }
            else {
                 
               //get the title and content of the post         
               $title2 = $post->post_title;
               $content = apply_filters("the_content", $post->post_content);
               
               global $post;
               $customCheck = get_post_meta($post->ID, 'id', true);          
               
               ////get username from saxo database
               $sql2 = "select ID, NAME, INITIALS from saxo.sx_userdata where name = '" . $user_login . "'";
               $stid2 = oci_parse($conn, $sql2);
               if (!$stid2) {
                        $e = oci_error($conn);
                        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
               }
               $result = oci_execute($stid2);
               
               if (!$result) {
                        $e = oci_error($stid2);
                        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
               }
               
            $row2 = oci_fetch_array($stid2, OCI_BOTH);
            
            //get correct publication for post
            $time = date('d/M/Y', strtotime($_POST['deadlineDate']));
            $sql4 = "select ID from saxo.sx_publication where trunc(releasedatetime) = '" . $time. "' and category = 428908922";
            
             $stid6 = oci_parse($conn, $sql4);
               if (!$stid6) {
                        $e = oci_error($conn);
                        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
               }
               $result = oci_execute($stid6);
               $row3 = oci_fetch_array($stid6, OCI_BOTH);
              
                     
            
            //set variables from saxo database for use in xml
            $publication = $row3['ID'];
            $userId = $row2['ID'];
            $userName = $row2['NAME'];
            $userLogin = $row2['INITIALS'];
            
            $product = $_POST['postArea'];
            $category = $_POST['postCategory'];
            $accessLevel = $_POST['postWorkflow'];
             $time = date('c', time());
                   
    
            //xml if posting story
            //when changing products make sure format/category etc. is set properly in xml
            $xmlStory2 =  "<sax:story xmlns:sax='http://www.saxotech.com/editorial'><sax:updatedtime></sax:updatedtime><sax:updatedby uri='http://10.81.2.77:8080/ews/products/95924151/users/" .
            $userId . "'/>
            <sax:site uri='http://10.81.2.77:8080/ews/products/" . $product . "'/><sax:status statuscode='1'/><sax:category uri='http://10.81.2.77:8080/ews/products/" . $product .
            "/stories/categories/" .$category . "'/>
            <sax:publication>" . $publication . "</sax:publication>
            <sax:accesslevel>" . $accessLevel . "</sax:accesslevel><sax:textformat>462702490</sax:textformat>
            <sax:title>" . $title2. "</sax:title><sax:description></sax:description><sax:author></sax:author><sax:kicker></sax:kicker>
            <sax:header></sax:header><sax:subheader></sax:subheader><sax:byline></sax:byline><sax:preface>
            </sax:preface><sax:netheader>
            <sax:plaintext></sax:plaintext></sax:netheader><sax:sms><sax:plaintext></sax:plaintext></sax:sms><sax:onlinepriority/>
            <sax:captions/><sax:changed content='false' properties='false' geometry='false'/><sax:paragraphs><sax:paragraph></sax:paragraph></sax:paragraphs>
            <sax:nativeboxtexts><sax:nativeboxtext boxtype='header' nativetexttype='Adobe InCopy CS4 ICML'>
            <sax:xhtml>
            <![CDATA[<?xml version='1.0' encoding='UTF-8' standalone='no'?><!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'><html
            xmlns='http://www.w3.org/1999/xhtml'><head><title/></head>
            <body>
            <p class='z-WebHead'></p></body></html>]]></sax:xhtml></sax:nativeboxtext><sax:nativeboxtext boxtype='body' nativetexttype='Adobe InCopy CS4 ICML'><sax:xhtml><![CDATA[<?xml
            version='1.0' encoding='UTF-8' standalone='no'?><!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'><html
            xmlns='http://www.w3.org/1999/xhtml'><head><title/></head><body>
            <p class='z-WebByline'>BY " . $userName . "</p>" . $content . "
            </body></html>]]>
            </sax:xhtml></sax:nativeboxtext></sax:nativeboxtexts><sax:bodyextent></sax:bodyextent><sax:numberofcharacters></sax:numberofcharacters>
            <sax:taxonomyterms/><sax:channels/><sax:multipriorities/></sax:story>";
            
 
            //check if story already exists in saxo db
            if ($customCheck)
            {                 
                    //xml if updating story        
                    $xmlStory3 =   "<sax:story id='" . $customCheck . "' uri='http://10.81.2.77:8080/ews/products/" . $product . "/stories/" . $customCheck ."'
                    xmlns:sax='http://www.saxotech.com/editorial'><sax:updatedtime>" . $time . "</sax:updatedtime><sax:updatedby
                    uri='http://10.81.2.77:8080/ews/products/" . $product . "/users/" . $userId . "'/><sax:site uri='http://10.81.2.77:8080/ews/products/" .
                    $product . "/sites/95924151'/><sax:status statuscode='1'/><sax:category uri='http://10.81.2.77:8080/ews/products/" . $product .
                    "/categories/" . $category . "'/><sax:publication>" . $publication ."</sax:publication><sax:accesslevel>" . $accessLevel . "</sax:accesslevel>
                    <sax:textformat>462702490</sax:textformat><sax:title>" . $title2 ."</sax:title><sax:description></sax:description>
                    <sax:author>" . $userName . "</sax:author><sax:kicker></sax:kicker><sax:header></sax:header><sax:subheader></sax:subheader>
                    <sax:byline></sax:byline><sax:preface></sax:preface><sax:netheader><sax:plaintext></sax:plaintext></sax:netheader><sax:sms>
                    <sax:plaintext></sax:plaintext></sax:sms><sax:onlinepriority/><sax:captions/><sax:changed content='false' properties='false'
                    geometry='false'/><sax:paragraphs/><sax:nativeboxtexts><sax:nativeboxtext boxtype='header' nativetexttype='Adobe InCopy CS4 ICML'>
                    <sax:xhtml><![CDATA[<?xml version='1.0' encoding='UTF-8' standalone='no'?>
                    <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
                    <html xmlns='http://www.w3.org/1999/xhtml'><head><title /></head><body><p class='z-WebHead'
                    /></body></html>]]></sax:xhtml></sax:nativeboxtext> <sax:nativeboxtext boxtype='body' nativetexttype='Adobe InCopy CS4
                    ICML'><sax:xhtml><![CDATA[<?xml version='1.0' encoding='UTF-8' standalone='no'?>
                    <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
                    <html xmlns='http://www.w3.org/1999/xhtml'><head><title /></head><body><p class='z-WebByline'>BY" . $userName . "</p><p class='z-WebBody'  >" . $content ."</p>
                    </body></html>]]></sax:xhtml></sax:nativeboxtext></sax:nativeboxtexts><sax:bodyextent></sax:bodyextent><sax:numberofcharacters>
                    </sax:numberofcharacters><sax:taxonomyterms/><sax:channels/><sax:multipriorities/><sax:websetting
                    id='449122403'><sax:subcategory></sax:subcategory><sax:webcategory/><sax:keywords>
                    </sax:keywords><sax:startpublicationtime>2013-12-14T08:00:00-06:00</sax:startpublicationtime>
                    <sax:stoppublicationtime></sax:stoppublicationtime>
                    <sax:factboxarticle></sax:factboxarticle><sax:frontpagedesign></sax:frontpagedesign><sax:listdesign>
                    </sax:listdesign><sax:articledesign></sax:articledesign>
                    <sax:pbcsextra></sax:pbcsextra><sax:topstory>0</sax:topstory><sax:forum>0</sax:forum>
                    <sax:usewebpublicationtime>1</sax:usewebpublicationtime><sax:priorityrotate>0</sax:priorityrotate>
                    <sax:multimedia>0</sax:multimedia><sax:storytype>0</sax:storytype></sax:websetting></sax:story>";                  
                    
                    //Put Story       

                    $userid = get_current_user_id();
                    $password = get_the_author_meta('saxopassword', $userid);
                    
                    set_time_limit(90);
                    
                 
                    $curlput = curl_init();                                         
                    curl_setopt($curlput, CURLOPT_URL,  "http://" .  $userLogin . ":" . $password . "@10.81.2.135:8080/ews/products/" . $product . "/stories/" . $customCheck);
                    curl_setopt($curlput, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curlput, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($curlput, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
                    curl_setopt($curlput, CURLOPT_POSTFIELDS, $xmlStory3);
                    curl_setopt($curlput, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($curlput, CURLOPT_VERBOSE, true);
                   
                    //$header_size = curl_getinfo($curlput, CURLINFO_HEADER_SIZE);
                    $chleadresult = curl_exec($curlput);
                    curl_close($curlput);
                    
                    //get header information for error testing
                    //$header = substr($chleadresult, 0, $header_size);
                    //$body = substr($chleadresult, $header_size);
                    //$file1 =  'header.txt';
                    //$story1 .= $header_size . PHP_EOL;
                    //$story1 .= $header . PHP_EOL;
                    //$story1 .= $body . PHP_EOL ;
                    //file_put_contents($file1, $story1, FILE_APPEND | LOCK_EX);
                   
                    //unlock story
                    //$unlockxml = "<sax:story xmlns:sax='http://www.saxotech.com/editorial'></sax:story>";
                    //
                    //$curl8 = curl_init();
                    //curl_setopt($curl8, curlOPT_URL, "http://" . $userLogin . ":saxo@10.81.2.77:8080/ews/products/" . $product . "/stories/" . $customCheck . "/unlock" );
                    //curl_setopt($curl8, curlOPT_PUT, 1);
                    //curl_setopt($curl8, curlOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
                    //curl_setopt($curl8, curlOPT_HEADER, 1);
                    //curl_setopt($curl8, curlOPT_INFILE, $putData);
                    //curl_setopt($curl8, curlOPT_INFILESIZE, strlen($unlockxml));
                    //$curl8_response = curl_exec($curl8);   
                    //curl_close($curl8);         
                    
                    
                    //if (isset($_FILES['file']))
                    //{
                    //    $fileName = $_FILES['file']['name'];
                    //    $fileType = $_FILES['file']['type'];
                    //    
                    //}
            }
            //if doesnt exist post story
            else{
            
                        //get login info for connecting to saxo 
                        global $user_login, $user_password;
                        get_currentuserinfo();
                        $userid = get_current_user_id();
                        $password = get_the_author_meta('saxopassword', $userid);
                        
                        //pass xml data into saxo
                        $f = fopen('request.txt', 'w');
                        $curl2 = curl_init();
                        curl_setopt($curl2, CURLOPT_URL, "http://" . $userLogin . ":" . $password . "@10.81.2.135:8080/ews/products/" . $product . "/stories");
                        curl_setopt($curl2, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
                        curl_setopt($curl2, CURLOPT_HEADER, 1);
                        curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl2, CURLOPT_POST, 1);
                        curl_setopt($curl2, CURLOPT_POSTFIELDS, $xmlStory2);
                        curl_setopt($curl2, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($curl2, CURLOPT_VERBOSE, true);
                        curl_setopt($curl2, CURLOPT_STDERR, $f);
                        $curl2_response = curl_exec($curl2);
                        $info = curl_getinfo($curl2);
                       
                        //get story id from returned header for creating story package link 
                        $headers = substr($curl2_response, 0, $info["header_size"]);
                        preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
                        preg_match("/(?<=stories\/)(\w+)/",$matches[1], $location);
                              
                        //add custom meta info for post to database
                        $locationHeader = $location[1];                                            
                        $postid = $post->ID;
                        add_post_meta($postid,'id',$locationHeader, true); 
                        add_post_meta($postid, 'product', $product, true);

                        
                        curl_close($curl2);  
                        
                        $html = "<html><body><p>";
            
                        //keep html logs of each category  
                        //switch ($category)
                        //{                  
                        //            case  610343469:
                        //            $catfile = 'ASectionLog.html';
                        //            $storyA .= $html . date("Y-m-d H:i:s") . "<br />";
                        //            $storyA .= $title2. "<br />";
                        //            $storyA .= substr($content,0,100) . "<br />";
                        //            //$storyA .= "</p></body></html>"
                        //            file_put_contents($catfile, $storyA, FILE_APPEND | LOCK_EX);
                        //            break;
                        //
                        //            case 877465922:
                        //            $personalfile = 'PersonalLog.html';
                        //            $storyP .= $html . date("Y-m-d H:i:s") . "<br />";
                        //            $storyP .= $title2. "<br />";
                        //            $storyP .=substr($content,0,100) . "<br />" ;
                        //            file_put_contents($personalfile, $storyP, FILE_APPEND | LOCK_EX);
                        //            break;               
                        //}
                        
                        //keep html log of all stories
                        //$file =  'log.html';
                        //$story .= $html . date("Y-m-d H:i:s") . "<br />";
                        //$story .= $product . "<br />";
                        //$story .= $category  . "<br />";
                        //$story .= $title2 . "<br />";
                        //$story .= substr($content,0,100) . "<br />" . "<br />" ;
                        //file_put_contents($file, $story, FILE_APPEND | LOCK_EX);
            }
             oci_free_statement($stid2);
             oci_close($conn);
             
              
                   
            }
}

//action that  posts a story Package
function postStoryPackage($post_ID, $post, $location)
{
           
           $title2 = $post_ID;            
           
           $xmlStoryPack = "<sax:planelement id='0' type='628986620' xmlns:sax='http://www.saxotech.com/editorial'><sax:name>" . $_POST['packageName'] . "</sax:name>
           <sax:summary>" . $_POST['packageSummary'] . "</sax:summary><sax:category uri='http://10.81.2.77:8080/ews/products/" . $product . "/planning/type/628986620/categories/" . $category . "'
           id='758085451'>Ottawa Packages</sax:category>
           <sax:startdatetime>" . $_POST['startDate'] . "</sax:startdatetime><sax:enddatetime>" . $_POST['endDate'] . "</sax:enddatetime><sax:deadlinedatetime>"
           . $_POST['deadlineDate'] . "</sax:deadlinedatetime>
           <sax:links><sax:link id='0' type='16'><sax:relid>" . $location[1] . "</sax:relid><sax:reltype>2</sax:reltype>
           <sax:relplantype>0</sax:relplantype></sax:link></sax:links></sax:planelement>";
           
            global $user_login;
            get_currentuserinfo();
            $uname = $user_login;
            
            //Post Story Package
            
            $curl3 = curl_init();
            curl_setopt($curl3, CURLOPT_URL, 'http://' . $uname . ':saxo@10.81.2.77:8080/ews/products/' . $publication . '/planning/type/628986620/elements');
            curl_setopt($curl3, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
            curl_setopt($curl3, CURLOPT_HEADER, 1);
            curl_setopt($curl3, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl3, CURLOPT_POSTFIELDS, $xmlStoryPack  );
             curl_setopt($curl3, CURLOPT_FOLLOWLOCATION, true);
            $curl3_response = curl_exec($curl3);                 
            
            //Put Story Package                             
            
            $xmlStoryPack2 = "<sax:planelement id='" . $location[1] . "' type='628986620' xmlns:sax='http://www.saxotech.com/editorial'><sax:name>" . $_POST['postTitle'] .
            "</sax:name><sax:summary>" . $_POST['postSummary'] . "</sax:summary>
            <sax:category uri='http://10.81.2.77:8080/ews/products/" . $product . "/planning/type/628986620/categories/" . $category . "' id='758085451'></sax:category>
            <sax:startdatetime>" . $_POST['startDate'] . "</sax:startdatetime>
            <sax:enddatetime>" . $_POST['endDate'] . "</sax:enddatetime><sax:deadlinedatetime>" . $_POST['deaadlineDate'] . "</sax:deadlinedatetime>
            </sax:planelement>";
            
            global $user_login;
            get_currentuserinfo();
            $uname = $user_login;
            
            $putData = fopen('php://temp/maxmemory:256000', 'w');  
                if (!$putData) {  
                    die('could not open temp memory data');  
                }  
            fwrite($putData, $xmlStoryPack2);  
            fseek($putData, 0);  
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://' . $uname . ':saxo@10.81.2.77:8080/ews/products/95924151/planning/type/628986620/elements/999140749');
            curl_setopt($curl, CURLOPT_PUT, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml;charset=UTF-8'));
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_INFILE, $putData);
            curl_setopt($curl, CURLOPT_INFILESIZE, strlen($xmlStoryPack2));
            $curl_response = curl_exec($curl);
}

//adds action to WordPress
add_action('add_meta_boxes', cdMetaBoxAdd);
add_action ('publish_post', postStory, 10, 2);


//add_action('pitch_to_draft', postStory);

?>