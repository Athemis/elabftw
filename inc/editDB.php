<?php
/**
 * inc/editDb.php
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see http://www.elabftw.net Official website
 * @license AGPL-3.0
 */

// ID
if (isset($_GET['id']) && !empty($_GET['id']) && is_pos_int($_GET['id'])) {
    $id = $_GET['id'];
    if (!item_is_in_team($id, $_SESSION['team_id'])) {
        die(_('This section is out of your reach.'));
    }
} else {
    display_message('error', _("The id parameter is not valid!"));
    require_once 'inc/footer.php';
    exit;
}

// GET CONTENT
    $sql = "SELECT items.*,
        items_types.bgcolor
        FROM items
        LEFT JOIN items_types ON (items.type = items_types.id)
        WHERE items.id = :id";
$req = $pdo->prepare($sql);
$req->bindParam(':id', $id);
$req->execute();
$data = $req->fetch();

// Check for lock
if ($data['locked'] == 1) {
    display_message('error', _('<strong>This item is locked.</strong> You cannot edit it.'));
    require_once 'inc/footer.php';
    exit;
}

// BEGIN CONTENT
?>
<script src="js/ckeditor/ckeditor.js"></script>
<section class='box' style='border-left: 6px solid #<?php echo $data['bgcolor']; ?>'>
    <!-- TRASH -->
    <img class='align_right' src='img/big-trash.png' title='delete' alt='delete' onClick="deleteThis('<?php echo $id; ?>','item', 'database.php')" />

    <?php
    // TAGS
    echo displayTags('items', $id);
    ?>

    <!-- BEGIN 2ND FORM -->
    <form method="post" action="app/editDB-exec.php" enctype='multipart/form-data'>
    <input name='item_id' type='hidden' value='<?php echo $id; ?>' />
    <div class='row'>

        <div class='col-md-4'>
        <img src='img/calendar.png' class='bot5px' title='date' alt='Date :' />
        <label for='datepicker'><?php echo _('Date'); ?></label>
        <!-- TODO if firefox has support for it: type = date -->
        <input name='date' id='datepicker' size='8' type='text' value='<?php echo $data['date']; ?>' />
        </div>
    </div>

    <!-- STAR RATING via ajax request -->
    <div class='align_right'>
    <input id='star1' name="star" type="radio" class="star" value='1' <?php if ($data['rating'] == 1) { echo "checked=checked "; }?>/>
    <input id='star2' name="star" type="radio" class="star" value='2' <?php if ($data['rating'] == 2) { echo "checked=checked "; }?>/>
    <input id='star3' name="star" type="radio" class="star" value='3' <?php if ($data['rating'] == 3) { echo "checked=checked "; }?>/>
    <input id='star4' name="star" type="radio" class="star" value='4' <?php if ($data['rating'] == 4) { echo "checked=checked "; }?>/>
    <input id='star5' name="star" type="radio" class="star" value='5' <?php if ($data['rating'] == 5) { echo "checked=checked "; }?>/>
    </div><!-- END STAR RATING -->

    <h4><?php echo _('Title'); ?></h4>
    <input id='title_input' name='title' rows="1" value='<?php echo stripslashes($data['title']); ?>' required />
    <h4><?php echo _('Infos'); ?></h4>
    <textarea class='mceditable' id='body_area' name='body' rows="15" cols="80">
        <?php echo $data['body']; ?>
    </textarea>
    <!-- SUBMIT BUTTON -->
    <div class='center' id='saveButton'>
        <button type="submit" name="Submit" class='button'><?php echo _('Save and go back'); ?></button>
    </div>
    </form>
    <!-- end edit items form -->
<span class='align_right'>
<?php
// get the list of revisions
$sql = "SELECT COUNT(*) FROM items_revisions WHERE item_id = :item_id ORDER BY savedate DESC";
$req = $pdo->prepare($sql);
$req->execute(array(
    'item_id' => $id
));
$rev_count = $req->fetch();
$count = intval($rev_count[0]);
if ($count > 0) {
    echo $count . " " . ngettext('revision available.', 'revisions available.', $count) . " <a href='revision.php?type=items&item_id=" . $id . "'>" . _('Show history') . "</a>";
}
?>
</span>

</section>

<?php
if ($_SESSION['prefs']['chem_editor']) {
    ?>
        <div class='box chemdoodle'>
            <h3><?php echo _('Molecule drawer'); ?></h3>
            <div class='center'>
                <script>
                    var sketcher = new ChemDoodle.SketcherCanvas('sketcher', 550, 300, {oneMolecule:true});
                </script>
            </div>
    </div>
    <?php
}
// FILE UPLOAD
require_once 'inc/file_upload.php';
// DISPLAY FILES
require_once 'inc/display_file.php';

// TAG AUTOCOMPLETE
$sql = "SELECT DISTINCT tag FROM items_tags WHERE team_id = :team_id ORDER BY id DESC LIMIT 500";
$getalltags = $pdo->prepare($sql);
$getalltags->bindParam(':team_id', $_SESSION['team_id']);
$getalltags->execute();
$tag_list = "";
while ($tag = $getalltags->fetch()) {
    $tag_list .= "'" . $tag[0] . "',";
}
?>

<script>
// DELETE TAG
function delete_tag(tag_id,item_id){
    var you_sure = confirm('<?php echo _('Delete this?'); ?>');
    if (you_sure == true) {
        $.post('app/delete.php', {
            id:tag_id,
            item_id:item_id,
            type:'itemtag'
        })
        .success(function() {$("#tags_div").load("database.php?mode=edit&id="+item_id+" #tags_div");})
    }
    return false;
}
// ADD TAG
function addTagOnEnter(e){ // the argument here is the event (needed to detect which key is pressed)
    var keynum;
    if(e.which)
        { keynum = e.which;}
    if(keynum == 13){  // if the key that was pressed was Enter (ascii code 13)
        // get tag
        var tag = $('#addtaginput').val();
        // POST request
        $.post('app/add.php', {
            tag: tag,
            item_id: <?php echo $id; ?>,
            type: 'itemtag'
        })
        // reload the tags list
        .success(function() {$("#tags_div").load("database.php?mode=edit&id=<?php echo $id; ?> #tags_div");
    // clear input field
    $("#addtaginput").val("");
    return false;
        })
    } // end if key is enter
}
// STAR RATINGS
function updateRating(rating) {
    // POST request
    $.post('app/star-rating.php', {
        star: rating,
        item_id: <?php echo $id; ?>
    })
    // reload the div
    .success(function () {
        return false;
    })
}

// READY ? GO !
$(document).ready(function() {
    // ADD TAG JS
    // listen keypress, add tag when it's enter
    $('#addtaginput').keypress(function (e) {
        addTagOnEnter(e);
    });

    // autocomplete the tags
    $("#addtaginput").autocomplete({
        source: [<?php echo $tag_list; ?>]
    });

    // If the title is 'Untitled', clear it on focus
    $("#title_input").focus(function(){
        if ($(this).val() === 'Untitled') {
            $("#title_input").val('');
        }
    });

    // EDITOR
    CKEDITOR.replace('body_area', {
        extraPlugins: 'mathjax',
        mathJaxLib: 'js/MathJax/MathJax.js?config=TeX-AMS_CHTML'
    });;
    // DATEPICKER
    $( "#datepicker" ).datepicker({dateFormat: 'yymmdd'});
    // STARS
    $('input.star').rating();
    $('#star1').click(function() {
        updateRating(1);
    });
    $('#star2').click(function() {
        updateRating(2);
    });
    $('#star3').click(function() {
        updateRating(3);
    });
    $('#star4').click(function() {
        updateRating(4);
    });
    $('#star5').click(function() {
        updateRating(5);
    });
    // fix for the ' and "
    title = "<?php echo $data['title']; ?>".replace(/\&#39;/g, "'").replace(/\&#34;/g, "\"");
    document.title = title;

    // ask the user if he really wants to navigate out of the page
<?php
if (isset($_SESSION['prefs']['close_warning']) && $_SESSION['prefs']['close_warning'] === 1) {
    echo "
window.onbeforeunload = function (e) {
      e = e || window.event;
      return '"._('Do you want to navigate away from this page? Unsaved changes will be lost!') . "';
};";
}
?>
});
</script>
