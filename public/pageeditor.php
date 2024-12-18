<?php require_once('../private/initialize.php'); ?>

<?php 
$page_title = 'open-lms'; 
include(SHARED_PATH . '/public-nav.php'); 
?>

<div class="container my-5">
<?php
// Directories for editable files and templates
$editableDirectory = '/var/www/html/public';
$templateDirectory = '/var/www/html/private/templates/blank';

// Ensure directories exist
if (!is_dir($editableDirectory)) {
    mkdir($editableDirectory, 0777, true);
}
if (!is_dir($templateDirectory)) {
    mkdir($templateDirectory, 0777, true);
}

// Get the list of editable files
$editableFiles = array_filter(scandir($editableDirectory), function($file) use ($editableDirectory) {
    return is_file("$editableDirectory/$file");
});

$templateFiles = array_filter(scandir($templateDirectory), function($file) use ($templateDirectory) {
    return is_file("$templateDirectory/$file");
});

// Handle new page creation (without editing content)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_file_name']) && !isset($_POST['content'])) {
    $newFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_POST['new_file_name']);
    $newFilePath = "$editableDirectory/$newFileName";

    if (isset($_POST['template']) && $_POST['template'] !== 'none') {
        $templatePath = "$templateDirectory/" . basename($_POST['template']);
        if (file_exists($templatePath)) {
            copy($templatePath, $newFilePath); // Copy template content
        }
    } else {
        // Insert the editable markers for a new blank file
        $defaultContent = "<!-- start-editable -->
                test<br>            <!-- end-editable -->";
        file_put_contents($newFilePath, $defaultContent);
    }
    // Refresh file list after creation
    $editableFiles = array_filter(scandir($editableDirectory), function($file) use ($editableDirectory) {
        return is_file("$editableDirectory/$file");
    });
}

// Handle file selection
$file = isset($_GET['file']) ? $_GET['file'] : (isset($editableFiles[0]) ? $editableFiles[0] : null);
$filePath = $file ? "$editableDirectory/$file" : null;

// Handle file saving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'], $_POST['file'])) {
    $fileToSave = "$editableDirectory/" . basename($_POST['file']);
    $originalContent = file_get_contents($fileToSave);
    $updatedEditable = $_POST['content'];

    // Replace only the portion within <!-- start-editable --> and <!-- end-editable -->
    $newContent = preg_replace('/(<!-- start-editable -->)(.*?)(<!-- end-editable -->)/is', '$1' . $updatedEditable . '$3', $originalContent, 1);
    file_put_contents($fileToSave, $newContent);
}

// Load the selected file content
$editableContent = '';
if ($filePath && file_exists($filePath)) {
    $originalContent = file_get_contents($filePath);
    // Extract the content between <!-- start-editable --> and <!-- end-editable -->
    if (preg_match('/<!-- start-editable -->(.*?)<!-- end-editable -->/is', $originalContent, $matches)) {
        $editableContent = $matches[1]; 
    } else {
        // If no markers found, use empty content or guide user to add markers
        $editableContent = '';
    }
}
?>

<div class="row mb-4">
    <div class="col-md-6 d-flex align-items-center">
        <?php if (!empty($editableFiles)): ?>
            <form method="get" class="form-inline mr-3">
                <label for="file" class="mr-2">Select a file:</label>
                <select name="file" id="file" class="form-control mr-2" onchange="this.form.submit()">
                    <?php foreach ($editableFiles as $fileOption): ?>
                        <option value="<?php echo htmlspecialchars($fileOption); ?>" <?php echo ($fileOption === $file) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($fileOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Load</button>
            </form>
        <?php else: ?>
            <p class="text-muted mb-0 mr-3">No editable files found in <strong><?php echo htmlspecialchars($editableDirectory); ?></strong>.</p>
        <?php endif; ?>

        <!-- Create New Page Button -->
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#newPageModal">
            Create New Page
        </button>
    </div>
</div>

<!-- Modal for New Page Creation -->
<div class="modal fade" id="newPageModal" tabindex="-1" role="dialog" aria-labelledby="newPageModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newPageModalLabel">Create a New Page</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <form method="post">
        <div class="modal-body">
            <div class="form-group">
                <label for="new_file_name">File Name:</label>
                <input type="text" id="new_file_name" name="new_file_name" placeholder="example.html" required class="form-control">
            </div>
            <div class="form-group">
                <label for="template">Copy from Template:</label>
                <select name="template" id="template" class="form-control">
                    <option value="none">None</option>
                    <?php foreach ($templateFiles as $template): ?>
                        <option value="<?php echo htmlspecialchars($template); ?>">
                            <?php echo htmlspecialchars($template); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Create Page</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if ($filePath): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Editing: <?php echo htmlspecialchars($file); ?></h5>
        </div>
        <div class="card-body">
            <!-- Simple WYSIWYG Toolbar -->
            <div class="btn-group mb-3" role="group" aria-label="Editor Toolbar">
                <button type="button" class="btn btn-sm btn-secondary" onclick="formatText('bold')"><b>B</b></button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="formatText('italic')"><i>I</i></button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="formatText('underline')"><u>U</u></button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="formatText('insertOrderedList')">OL</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="formatText('insertUnorderedList')">UL</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="formatText('createLink', prompt('Enter URL:','https://'))">Link</button>
            </div>
            
            <!-- Contenteditable Div -->
            <div id="editor" contenteditable="true" class="form-control" style="min-height:200px; background:#fff;">
                <?php echo htmlspecialchars_decode($editableContent); ?>
            </div>
        </div>
        <div class="card-footer">
            <form method="post" id="editor-form" class="mb-0">
                <textarea name="content" id="hidden-content" style="display:none;"></textarea>
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                <button type="submit" onclick="saveContent()" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
<?php elseif(empty($editableFiles)): ?>
    <p class="text-muted">Add files to the <strong><?php echo htmlspecialchars($editableDirectory); ?></strong> directory to edit them.</p>
<?php endif; ?>
</div>

<script>
function formatText(command, value = null) {
    document.execCommand(command, false, value);
}

function saveContent() {
    var editor = document.getElementById('editor');
    var hiddenContent = document.getElementById('hidden-content');
    hiddenContent.value = editor.innerHTML;
}
</script>

<!-- start-editable -->
<div class="container-fluid text-center" id="editable-content">
  Your editable content here.
</div>
<!-- end-editable -->

<?php include(SHARED_PATH . '/public-footer.php'); ?>