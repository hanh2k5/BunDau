<?php

namespace App\Services;

use App\Exceptions\ProductInUseException;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $repo
    ) {}

    /**
     * List all products with optional filtering.
     */
    public function list(array $filters = []): Collection
    {
        return $this->repo->all($filters);
    }

    /**
     * Get a single product by ID.
     */
    public function find(int $id): Product
    {
        return $this->repo->findOrFail($id);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        if (isset($data['image'])) {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            
            if ($cloudName) {
                $cloudinary = new Cloudinary([
                    'cloud' => [
                        'cloud_name' => $cloudName,
                        'api_key'    => env('CLOUDINARY_API_KEY'),
                        'api_secret' => env('CLOUDINARY_API_SECRET'),
                    ],
                ]);
                $result = $cloudinary->uploadApi()->upload($data['image']->getRealPath(), [
                    'folder' => 'products'
                ]);
                $data['image_path'] = $result['secure_url'];
            } else {
                // Fallback to local storage if Cloudinary is not configured
                $path = $data['image']->store('products', 'public');
                $data['image_path'] = asset('storage/' . $path);
            }
            unset($data['image']);
        }

        return $this->repo->create($data);
    }

    /**
     * Update a product.
     */
    public function update(Product $product, array $data): Product
    {
        if (isset($data['original_updated_at']) && $product->updated_at) {
            // Compare timestamps
            if ($product->updated_at->toIso8601String() !== $data['original_updated_at']) {
                abort(409, 'Dữ liệu sản phẩm đã bị thay đổi bởi người khác, vui lòng tải lại trang.');
            }
        }

        // Handle image update
        if (isset($data['image'])) {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            
            if ($cloudName) {
                $cloudinary = new Cloudinary([
                    'cloud' => [
                        'cloud_name' => $cloudName,
                        'api_key'    => env('CLOUDINARY_API_KEY'),
                        'api_secret' => env('CLOUDINARY_API_SECRET'),
                    ],
                ]);
                $result = $cloudinary->uploadApi()->upload($data['image']->getRealPath(), [
                    'folder' => 'products'
                ]);
                $data['image_path'] = $result['secure_url'];
            } else {
                // Fallback to local storage
                $path = $data['image']->store('products', 'public');
                $data['image_path'] = asset('storage/' . $path);
            }
            unset($data['image']);
        } elseif (isset($data['delete_image']) && $data['delete_image']) {
            $data['image_path'] = null;
        }

        return $this->repo->update($product, $data);
    }

    /**
     * Toggle product availability.
     */
    public function toggleAvailability(Product $product): Product
    {
        return $this->repo->toggleAvailability($product);
    }

    /**
     * Delete a product (soft delete).
     *
     * @throws ProductInUseException
     */
    public function delete(Product $product): bool
    {
        if ($this->repo->hasPendingOrders($product)) {
            throw new ProductInUseException('Không thể xóa sản phẩm đang được sử dụng trong đơn hàng chờ xử lý');
        }

        return $this->repo->delete($product);
    }
}
