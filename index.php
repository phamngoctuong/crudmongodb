<!DOCTYPE html>
<html lang="en">

<head>
 <meta charset="UTF-8">
 <title>MongoDB</title>
 <link rel="stylesheet" href="css/bootstrap.min.css">
 <script type="text/javascript" src="js/jquery.min.js"></script>
 <script type="text/javascript" src="js/bootstrap.min.js"></script>
</head>

<body>
 <div class="container">
  <h1 class="text-center">CRUD operation on MongoDB in PHP using mogodb library</h1>
  <h2 class="text-center">MongoDB on Windows 10</h2>
  <?php  
		require_once "vendor/autoload.php";
		$client 	= new MongoDB\Client;
		$dataBase 	= $client->selectDatabase('blog');
		$collection = $dataBase->selectCollection('articles');
		if(isset($_POST['create'])) {
			$data 		= [
				'title' 		=> $_POST['title'],
				'description' 	=> $_POST['description'],
				'author' 		=> $_POST['author'],
				'createdOn' 	=> new MongoDB\BSON\UTCDateTime
			];
			if($_FILES['file']) {
				if(move_uploaded_file($_FILES['file']['tmp_name'], 'upload/'.$_FILES['file']['name'])) {
					$data['fileName'] = $_FILES['file']['name'];
				} else {
					echo "Failed to upload file.";
				}
			}
			$result = $collection->insertOne($data);
			if($result->getInsertedCount()>0) {
				echo "Article is created..";
			} else {
				echo "Failed to create Article";
			}
		}
		if(isset($_POST['update'])) {
			$filter		= ['_id' => new MongoDB\BSON\ObjectId($_POST['aid'])];
			$data 		= [
				'title' 		=> $_POST['title'],
				'description' 	=> $_POST['description'],
				'author' 		=> $_POST['author']
			];
			$result = $collection->updateOne($filter, ['$set' => $data]);
			if($result->getModifiedCount()>0) {
				echo "Article is updated..";
			} else {
				echo "Failed to update Article";
			}
		}
		if(isset($_GET['action']) && $_GET['action'] == 'delete') {		
			$filter		= ['_id' => new MongoDB\BSON\ObjectId($_GET['aid'])];
			$article = $collection->findOne($filter);
			if(!$article) {
				echo "Article not found.";
			}
			$fileName = 'upload/'.$article['fileName'];
			if(file_exists($fileName)) {
				if(!unlink($fileName)) {
					echo "Failed to delete file."; 
					exit;
				}
			}
			$result = $collection->deleteOne($filter);
			if($result->getDeletedCount()>0) {
				echo "Article is deleted..";
				header("Location: http://localhost/mongodb");
			} else {
				echo "Failed to delete Article";
			}
		}
  ?>
  <div class="row">
	  <div class="col-md-4">
	   <form class="form-horizontal" method="post" action="" enctype="multipart/form-data">
	    <fieldset>
	     <!-- Form Name -->
	     <legend style="margin-top: 5px; padding-top: 0;">Article Details</legend>
	     <!-- Text input-->
	     <div class="form-group">
	      <label class="col-md-12" for="title">Title</label>
	      <div class="col-md-12">
	       <input id="title" name="title" type="text" placeholder="" class="form-control input-md">
	      </div>
	     </div>
	     <!-- Text Area-->
	     <div class="form-group">
	      <label class="col-md-12" for="description">Description</label>
	      <div class="col-md-12">
	       <textarea id="description" name="description" placeholder="" class="form-control" rows="6"></textarea>
	      </div>
	     </div>
	     <!-- Text input-->
	     <div class="form-group">
	      <label class="col-md-12" for="author">Author</label>
	      <div class="col-md-12">
	       <input id="author" name="author" type="text" placeholder="" class="form-control input-md">
	      </div>
	     </div>
	     <!-- File input-->
	     <div class="form-group" id="fileInput">
	      <label class="col-md-12" for="file">Select Image</label>
	      <div class="col-md-12">
	       <input id="file" name="file" type="file" placeholder="" class="form-control input-md">
	      </div>
	     </div>
	     <!-- Hidden article id -->
	     <input type="hidden" name="aid" id="aid">
	     <button id="create" name="create" class="btn btn-primary">Create Article</button>
	     <button id="update" style="display: none;" name="update" class="btn btn-primary">Update Article</button>
	    </fieldset>
	   </form>
	  </div>
	  <div class="col-md-8">
	  	<?php  
	  		$articles = $collection->find();
	  		foreach ($articles as $key => $article) {
  			$UTCDateTime 	= new MongoDB\BSON\UTCDateTime((string)$article['createdOn']);
	    	$DateTime 		= $UTCDateTime->toDateTime();
	    	$data = json_encode( [
					'id' 			=> (string) $article['_id'],
					'title' 		=> $article['title'],
					'description' 	=> $article['description'],
					'author' 		=> $article['author']
				], true);
	  	?>
		   	<div class="row">
					<div class="col-md-12"><?php echo $DateTime->format('d/m/Y H:i:s'); ?></div>
					<div class="row">
						<div class="col-md-3">
							<img src="./upload/<?php echo $article['fileName']; ?>" width="100%">
						</div>
						<div class="col-md-8">
							<strong><?php echo $article['title']; ?></strong>
							<p><?php echo $article['description']; ?></p>
							<p class="text-right"><?php echo $article['author']; ?></p>
						</div>
						<div class="col-md-1">
							<a href='javascript:updateArticle(<?php echo $data; ?>)'>Edit</a><br>
							<a href="index.php?action=delete&aid=<?php echo $article['_id'];  ?>">Delete</a>
						</div>
					</div>
				</div>
	  	<?php
	  		}
	  	?>
	  </div>
 	</div>
 </div>
 <script type="text/javascript">
	function updateArticle(article) {
		console.log(article);
		$('#aid').val(article.id);
		$('#title').val(article.title);
		$('#description').val(article.description);
		$('#author').val(article.author);
		$('#create').hide();
		$('#fileInput').hide();
		$('#update').show();
	}
</script>
</body>
</html>