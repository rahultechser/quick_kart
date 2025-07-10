<?php
// --- AJAX HANDLER ---
require_once __DIR__ . '/../common/config.php';
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'An error occurred'];

    if (isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'update')) {
        $id = (int)($_POST['id'] ?? 0);
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $image_name = $_POST['existing_image'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $image_name = time() . '_product_' . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
        }

        if ($_POST['action'] == 'add') {
            $stmt = $conn->prepare("INSERT INTO products (name, category_id, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisdis", $name, $category_id, $description, $price, $stock, $image_name);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sisdisi", $name, $category_id, $description, $price, $stock, $image_name, $id);
        }
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Product saved successfully.'];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
    elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Product deleted.'];
        } else {
            $response['message'] = 'Failed to delete product.';
        }
        $stmt->close();
    }
    
    echo json_encode($response);
    exit;
}

// --- HTML PAGE ---
require_once 'common/header.php';
$products = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div class="bg-white p-6 rounded-lg shadow-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Manage Products</h2>
        <button id="add-product-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add New Product</button>
    </div>

    <!-- Product Form (Modal) -->
    <div id="product-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-2xl my-8">
            <h3 id="modal-title" class="text-xl font-bold mb-4">Add Product</h3>
            <form id="product-form" enctype="multipart/form-data">
                <input type="hidden" name="id" id="product-id">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="existing_image" id="existing-image">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block">Product Name</label>
                        <input type="text" name="name" id="product-name" class="w-full px-3 py-2 border rounded" required>
                    </div>
                    <div>
                        <label class="block">Category</label>
                        <select name="category_id" id="product-category" class="w-full px-3 py-2 border rounded" required>
                            <?php $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block">Price</label>
                        <input type="number" step="0.01" name="price" id="product-price" class="w-full px-3 py-2 border rounded" required>
                    </div>
                     <div>
                        <label class="block">Stock</label>
                        <input type="number" name="stock" id="product-stock" class="w-full px-3 py-2 border rounded" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block">Description</label>
                        <textarea name="description" id="product-description" rows="4" class="w-full px-3 py-2 border rounded"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block">Product Image</label>
                        <input type="file" name="image" id="product-image" class="w-full">
                         <img id="image-preview" src="" class="mt-2 h-20 hidden"/>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" id="cancel-btn" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Image</th>
                    <th class="py-3 px-4 text-left">Name</th>
                    <th class="py-3 px-4 text-left">Category</th>
                    <th class="py-3 px-4 text-left">Price</th>
                    <th class="py-3 px-4 text-left">Stock</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="product-table-body">
                <?php while($prod = $products->fetch_assoc()): ?>
                <tr class="border-b">
                    <td class="py-2 px-4"><img src="uploads/<?php echo $prod['image']; ?>" class="h-12 w-12 object-cover rounded"></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($prod['name']); ?></td>
                    <td class="py-2 px-4"><?php echo htmlspecialchars($prod['category_name']); ?></td>
                    <td class="py-2 px-4">$<?php echo $prod['price']; ?></td>
                    <td class="py-2 px-4"><?php echo $prod['stock']; ?></td>
                    <td class="py-2 px-4 whitespace-nowrap">
                        <button class="edit-btn text-blue-500 hover:text-blue-700 mr-2" data-product='<?php echo json_encode($prod); ?>'><i class="fas fa-edit"></i></button>
                        <button class="delete-btn text-red-500 hover:text-red-700" data-id="<?php echo $prod['id']; ?>"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
// This JS is very similar to category.php's JS, adapted for product fields
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('product-modal');
    const addBtn = document.getElementById('add-product-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const form = document.getElementById('product-form');

    const showModal = () => modal.classList.remove('hidden');
    const hideModal = () => {
        modal.classList.add('hidden');
        form.reset();
        document.getElementById('image-preview').classList.add('hidden');
    };

    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').innerText = 'Add Product';
        document.getElementById('form-action').value = 'add';
        showModal();
    });
    
    cancelBtn.addEventListener('click', hideModal);

    document.getElementById('product-table-body').addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) {
            const product = JSON.parse(editBtn.dataset.product);
            document.getElementById('modal-title').innerText = 'Edit Product';
            document.getElementById('form-action').value = 'update';
            document.getElementById('product-id').value = product.id;
            document.getElementById('product-name').value = product.name;
            document.getElementById('product-category').value = product.category_id;
            document.getElementById('product-price').value = product.price;
            document.getElementById('product-stock').value = product.stock;
            document.getElementById('product-description').value = product.description;
            document.getElementById('existing-image').value = product.image;
            const preview = document.getElementById('image-preview');
            preview.src = `uploads/${product.image}`;
            preview.classList.remove('hidden');
            showModal();
        }
        
        const deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
             if (confirm('Are you sure?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', deleteBtn.dataset.id);
                fetch('product.php', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
                    .then(res => res.json()).then(data => data.status === 'success' ? location.reload() : alert(data.message));
            }
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        fetch('product.php', { method: 'POST', body: formData, headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(res => res.json()).then(data => data.status === 'success' ? location.reload() : alert(data.message));
    });
});
</script>
<?php require_once 'common/bottom.php'; ?>