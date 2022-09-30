<?php 
   require_once('crawler.php');
   
   ?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <title>Web Crawler</title>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
      <!-- UIkit CSS -->
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.7.0/dist/css/uikit.min.css" />
      <link rel="stylesheet" type="text/css" href="css/
         style.css">
      <!-- UIkit JS -->
      <script src="https://cdn.jsdelivr.net/npm/uikit@3.7.0/dist/js/uikit.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/uikit@3.7.0/dist/js/uikit-icons.min.js"></script>
   </head>
   <body>
      <div class="uk-container uk-margin-medium-top">
         <h3 class="uk-text-primary"><strong><?= $crawlUrl; ?></strong> </h3>
         <p><strong>Max Crawled Page:</strong> <?= $pageCrawl; ?> </p>
         <div class="uk-card uk-card-default uk-card-body">
            <?php 
               $total_url = [];
               $word_count = 0;
               $avg_load=0;
               $text_len=0;
               ?>
            <table class="uk-table uk-table-divider uk-table-striped">
               <thead>
                  <tr>
                     <th>
                        Number of pages crawled
                     </th>
                     <th>
                        Avg page load
                     </th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach($json as $jsons){
                     $total_url[] =  $jsons->url;
                     $word_count += intval($jsons->word_count);
                     $avg_load += $jsons->load;
                     }?>
                  <tr>
                     <td>
                        <?= array_sum(array_count_values($total_url)); ?>
                     </td>
                     <td>
                        <?= $avg_load; ?>
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>
         <div class="uk-card uk-card-default uk-card-body uk-margin-medium-top">
            <table class="uk-table uk-table-divider uk-table-striped">
               <thead>
                  <tr>
                     <th>
                        Crawled Url
                     </th>
                     <th>
                        Crawled URL Images
                     </th>
                     <th>
                        Status code
                     </th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach($json as $json_data){ ?>
                  <tr>
                     <td>
                        <?= urldecode($json_data->url); ?>
                     </td>
                     <td>
                        <?php foreach($json_data->images as $url_images){ ?>
                        <?php 
                           $url = urldecode($url_images); 
                           $self_url = urldecode($json_data->url);
                           $img = substr($url, 0, 1) === '/' ? $self_url.$url : $url;
                           ?>
                        <a href="<?= $img; ?>" target="_blank"><?= $img; ?></a>
                        <?php } ?>
                     </td>
                     <td>
                        <?= $json_data->status_code[0]; ?>
                     </td>
                     <?php
                        $images[] = $json_data->images;
                        ?>
                  </tr>
                  <?php } ?>
               </tbody>
            </table>
         </div>
         <div class="uk-card uk-card-default uk-card-body uk-margin-medium-top">
            <table class="uk-table uk-table-divider uk-table-striped">
               <thead>
                  <tr>
                     <th>
                        Crawled images
                     </th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach($json as $json_datas){ ?>
                  <?php foreach($json_datas->images as $image){ ?>
                  <tr class="img">
                     <?php
                        $url = urldecode($image); 
                        $self_url = urldecode($json_data->url);
                        $img = substr($url, 0, 1) === '/' ? $self_url.$url : $url;
                        ?>
                     <td>
                        <?= $img; ?>
                     </td>
                  </tr>
                  <?php } } ?>
               </tbody>
            </table>
         </div>
      </div>
   </body>
</html>