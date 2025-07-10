<?php
// --- AJAX HANDLER ---
// This part must be at the very top, before any HTML output.
require_once __DIR__ . '/../common/config.php';
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An error occurred'];

    // Action: Add/Update Category
    if (isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'update')) {
        $name = $_POST['name'];
        $id = (int)($_POST['id'] ?? 0);
        $image_name = $_POST['existing_image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image_name = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        }

        if ($_POST['action'] == 'add') {
            $stmt = $conn->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $image_name);
        } else {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $image_name, $id);
        }
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Category saved successfully.'];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
    // Action: Delete Category
    elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Category deleted.'];
        } else {
            $response['message'] = 'Failed to delete category.';
        }
        $stmt->close();
    }

    echo json_encode($response);
    exit;
}

// --- HTML PAGE ---
require_once 'common/header.php';
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Manage Categories</h2>
        <button id="add-category-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add New Category</button>
    </div>

    <!-- Category Form (Modal) -->
    <div id="category-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
            <h3 id="modal-title" class="text-xl font-bold mb-4">Add Category</h3>
            <form id="category-form" enctype="multipart/form-data">
                <input type="hidden" name="id" id="category-id">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="existing_image" id="existing-image">
                <div class="mb-4">
                    <label class="block text-gray-700">Category Name</label>
                    <input type="text" name="name" id="category-name" class="w-full px-3 py-2 border rounded" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Category Image</label>
                    <input type="file" name="image" id="category-image" class="w-full">
                    <img id="image-preview" src="" class="mt-2 h-20 hidden"/>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancel-btn" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Image</th>
                    <th class="py-3 px-4 text-left">Name</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="category-table-body">
                <?php while($cat = $categories->fetch_assoc()): ?>
                <tr class="border-b" id="cat-row-<?php echo $cat['id']; ?>">
                    <td class="py-3 px-4"><img src="uploads/<?php echo $cat['image']; ?>" class="h-12 w-12 object-cover rounded"></td>
                    <td class="py-3 px-4"><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td class="py-3 px-4">
                        <button class="edit-btn text-blue-500 hover:text-blue-700 mr-2" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo htmlspecialchars($cat['name']); ?>" data-image="<?php echo $cat['image']; ?>"><i class="fas fa-edit"></i></button>
                        <button class="delete-btn text-red-500 hover:text-red-700" data-id="<?php echo $cat['id']; ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('category-modal');
    const addBtn = document.getElementById('add-category-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const form = document.getElementById('category-form');

    const showModal = () => modal.classList.remove('hidden');
    const hideModal = () => {
        modal.classList.add('hidden');
        form.reset();
        document.getElementById('image-preview').classList.add('hidden');
    };

    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').innerText = 'Add Category';
        document.getElementById('form-action').value = 'add';
        showModal();
    });

    cancelBtn.addEventListener('click', hideModal);

    document.getElementById('category-table-body').addEventListener('click', (e) => {
        if (e.target.closest('.edit-btn')) {
            const btn = e.target.closest('.edit-btn');
            document.getElementById('modal-title').innerText = 'Edit Category';
            document.getElementById('form-action').value = 'update';
            document.getElementById('category-id').value = btn.dataset.id;
            document.getElementById('category-name').value = btn.dataset.name;
            document.getElementById('existing-image').value = btn.dataset.image;
            const preview = document.getElementById('image-preview');
            preview.src = `uploads/${btn.dataset.image}`;
            preview.classList.remove('hidden');
            showModal();
        }
        
        if (e.target.closest('.delete-btn')) {
            const btn = e.target.closest('.delete-btn');
            if (confirm('Are you sure you want to delete this category?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', btn.dataset.id);
                fetch('category.php', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        fetch('category.php', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    hideModal();
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    });
});
</script>
<?php require_once 'common/bottom.php'; ?>