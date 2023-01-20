<?php
$document = $PAGE["document"];
$data = $document->data;
global $CONTENT;
use Prismic\Dom\RichText;
/* ---------------
echo "<pre>";
print_r($document);
echo "</pre>";w
/* --------------- */
?>

<?php
$catData = [];
$allWork = getByType("work", "first_publication_date desc");

foreach($allWork as $key => $work) {
    $thisProject = $work->data;
    echo $thisProject->project_title[0]->text;
}

?>


<div class="projects-section sticky-inside c-24">
	<div class="projects-section-copy" id="projects-section-copy">
		<span></span>
		<span></span>
        <div class="top">
            <div class="left">Our select <b><span> web</span> work</b><svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="5" fill="#F09251"/></svg></div>
            <div class="right">
                <button class="select">Web [8]</button>
            </div>
            <div class="right-mobile">
                <select>
                    <option><svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="4" cy="4" r="4" fill="#F09251"/></svg>Web</option>
                    <option>CGI Motion</option>
                    <option>Branding</option>
                    <option>Strategy</option>
                </select>
            </div>
        </div>
		<div class="gradient-area mw" id="gradient-area"></div>
	</div>
    <div class="projects-section-inner">
		<?php foreach ($data->projects as $project) {
			$thisDocument = $CONTENT->local->getContent($project->project->uid, "work");
			$thisData = $thisDocument->data;
				include(views_dir() . "/parts/project-block.php");
		} ?>
	</div>
</div>

