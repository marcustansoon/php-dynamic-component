<div>
	<form action="/action_page.php">
		<?= rand(0,100) > 50 ? 'random1' : '<textarea>ddd</textarea>' ?>
		<?= rand(0,100) > 20 ? 'random2' : '1' ?>
		<label for="fname">First name:</label><br>
		<?= $fname ?>
		<input type="text" id="fname" name="fname" d-bind-model="fname"><br>
		<label for="lname">Last name:</label><br>
		<input type="text" id="lname" name="lname"  d-bind-model="lname"><br><br>
		<input type="submit" value="Submit">
	</form>
</div>