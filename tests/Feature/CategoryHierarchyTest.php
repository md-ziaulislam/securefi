<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create([
            'name' => 'Editor Admin',
            'email' => 'editor@securofi.tech',
        ]);
        $this->admin->assignRole('Admin');
    }

    public function test_admin_can_view_category_management_screen(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertOk();
        $response->assertSee('Taxonomy & Category Hierarchy');
        $response->assertSee('Parent Category (Hierarchy)');
    }

    public function test_admin_can_create_main_category_and_subcategory(): void
    {
        // 1. Create Main Category
        $response1 = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Cybersecurity Hub',
            'slug' => 'cybersecurity-hub',
            'parent_id' => null,
            'order' => 1,
            'is_active' => '1',
        ]);
        $response1->assertRedirect();
        $response1->assertSessionHas('success');

        $mainCat = Category::where('slug', 'cybersecurity-hub')->first();
        $this->assertNotNull($mainCat);
        $this->assertTrue($mainCat->isParent());
        $this->assertNull($mainCat->parent_id);

        // 2. Create Subcategory under Main Category
        $response2 = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Zero Trust Architecture',
            'slug' => 'zero-trust-architecture',
            'parent_id' => $mainCat->id,
            'order' => 1,
            'is_active' => '1',
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHas('success');

        $subCat = Category::where('slug', 'zero-trust-architecture')->first();
        $this->assertNotNull($subCat);
        $this->assertTrue($subCat->isSubcategory());
        $this->assertEquals($mainCat->id, $subCat->parent_id);
        $this->assertEquals('Cybersecurity Hub', $subCat->parent->name);
        $this->assertCount(1, $mainCat->children);
    }

    public function test_subcategories_render_directly_underneath_main_category_in_admin_table(): void
    {
        $main1 = Category::create(['name' => 'Alpha Main', 'slug' => 'alpha-main', 'order' => 1, 'is_active' => true]);
        $sub1 = Category::create(['name' => 'Alpha Sub 1', 'slug' => 'alpha-sub-1', 'parent_id' => $main1->id, 'order' => 1, 'is_active' => true]);
        $sub2 = Category::create(['name' => 'Alpha Sub 2', 'slug' => 'alpha-sub-2', 'parent_id' => $main1->id, 'order' => 2, 'is_active' => true]);
        $main2 = Category::create(['name' => 'Beta Main', 'slug' => 'beta-main', 'order' => 2, 'is_active' => true]);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertOk();

        // In the table body HTML, Alpha Main, Alpha Sub 1, Alpha Sub 2, and Beta Main appear in hierarchical sequence
        $content = $response->getContent();
        $tableBody = substr($content, strpos($content, '<tbody'));
        $posMain1 = strpos($tableBody, 'Alpha Main');
        $posSub1 = strpos($tableBody, 'Alpha Sub 1');
        $posSub2 = strpos($tableBody, 'Alpha Sub 2');
        $posMain2 = strpos($tableBody, 'Beta Main');

        $this->assertTrue($posMain1 !== false && $posSub1 !== false && $posSub2 !== false && $posMain2 !== false);
        $this->assertTrue($posMain1 < $posSub1);
        $this->assertTrue($posSub1 < $posSub2);
        $this->assertTrue($posSub2 < $posMain2);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $cat = Category::create(['name' => 'Standalone Cat', 'slug' => 'standalone-cat', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $cat->id), [
            'name' => 'Standalone Cat',
            'slug' => 'standalone-cat',
            'parent_id' => $cat->id,
        ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertNull($cat->fresh()->parent_id);
    }

    public function test_cannot_delete_main_category_with_subcategories(): void
    {
        $main = Category::create(['name' => 'Parent Cat', 'slug' => 'parent-cat', 'is_active' => true]);
        $sub = Category::create(['name' => 'Child Cat', 'slug' => 'child-cat', 'parent_id' => $main->id, 'is_active' => true]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $main->id));
        $response->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $main->id]);
        $this->assertDatabaseHas('categories', ['id' => $sub->id]);
    }
}
