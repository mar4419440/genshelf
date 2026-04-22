# تقرير أخطاء GenShelf (Bug Report)

نظام POS + inventory بلوجيك solid كويس، بس في bugs هتأثر على بيانات الـ production.

## ملخص الحالة (Overall Stats)
- **Critical (حرجة):** 4 🔴
- **Major (كبيرة):** 5 🟡
- **Minor (بسيطة):** 4 🟢

---

## 🔴 Critical — بيكسر Data

### Username column غير موجود في DB
*   **الموقع:** `UserController.php → storeUser / updateUser` | `Migration: 0001_01_01_000000`
*   **الوصف:** الـ `UserController` بيعمل validate على `'username'` وبيحاول يعمل `unique:users,username`، لكن الـ `users` table مفيهاش column اسمه `username` خالص — عندها بس `name` وبعدين `display_name`. أي attempt لإنشاء أو تعديل user هيطلع SQL error فوري.

### Low-stock query مش شغال (HAVING بدون GROUP BY)
*   **الموقع:** `DashboardController.php → index` | `ReportController.php → getDashboardData`
*   **الوصف:** الـ `whereExists` subquery بيستخدم `havingRaw('SUM(product_batches.qty) <= ...')` من غير `groupBy` — ده invalid SQL على معظم databases وهيطلع 0 أو error. المفروض يتحول لـ `whereRaw` مع `EXISTS (SELECT 1 ... GROUP BY product_id HAVING SUM(qty) <= ...)`.

### Expense model مش بيحفظ description_en
*   **الموقع:** `Expense.php → $fillable` | `FinanceController.php → storeExpense`
*   **الوصف:** الـ `FinanceController` بيبعت `description_en` و`user_id`، لكن الـ `Expense` model عنده `$fillable` مكتوب صريح وناقصين الاتنين دول. يعني بيتعمل save من غير user_id أو الترجمة الإنجليزية.

### Storage delete بيشيل الـ batches بدون تحذير
*   **الموقع:** `StorageController.php → destroy`
*   **الوصف:** الكود فيه comment "check if there are active batches" بس التنفيذ الفعلي مش موجود — بيعمل `$storage->delete()` مباشرة. لو الـ FK مش `restrict`، هيمسح كل الـ inventory batches المرتبطة بـ storage كامل بضربة واحدة.

---

## 🟡 Major — Logic Issues

### N+1 Query في POS index
*   **الموقع:** `PosController.php → index`
*   **الوصف:** بيجيب كل الـ products بـ `DB::table('products')->get()` وبعدين لكل product بيعمل query تانية `DB::table('product_batches')->where(...)->sum('qty')`. لو عندك 200 product = 201 query. الحل: `Product::withSum('batches', 'qty')`.

### SpecialOffer: active vs is_active inconsistency
*   **الموقع:** `SpecialOffer.php` | `OfferController.php` | `Migration`
*   **الوصف:** الـ migration بيعمل column اسمه `active`، لكن الـ model فيه `$casts = ['is_active' => 'boolean']`. الـ controller بيكتب في `active` بصح، لكن أي Blade view بيقرأ `$offer->is_active` هيلاقي `null` دايماً.

### Transfer route مكررة + destroy مش موجود
*   **الموقع:** `routes/web.php` | `TransferController.php`
*   **الوصف:** `Route::resource('transfers', ...)` متسجل مرتين في الـ routes. كمان الـ `TransferController` عنده `index` و`store` بس — مفيش `destroy` method، فلو حد بعت DELETE request هيطلع 404 أو error.

### FIFO stock deduction مش بتبدأ من الـ oldest batch
*   **الموقع:** `PosController.php → checkout`
*   **الوصف:** الـ FIFO بيعمل `orderBy('expiration_date', 'asc')` لكن products من غير expiration date هيطلعوا في الأول (NULL جاي قبل أي date في SQL). المفروض: `orderBy('expiration_date', 'asc')->orderBy('created_at', 'asc')` أو `NULLS LAST` لو الـ DB بيدعمها.

### Settings بتتجاب في كل request (N queries)
*   **الموقع:** `PosController`, `InventoryController`, `DashboardController`, ...
*   **الوصف:** كل page بتعمل 3-5 calls منفصلة زي `DB::table('settings')->where('key', 'tax_rate')->value('value')`. لو في 10 pages × 5 queries = 50 unnecessary setting queries. الأسهل: `Setting::pluck('value','key')` مرة واحدة في AppServiceProvider أو Middleware وتحط النتيجة في `config()`.

---

## 🟢 Minor — Code Quality

### Route migrate عامل في authenticated group
*   **الموقع:** `routes/web.php`
*   **الوصف:** `/migrate` route بتعمل `Artisan::call('migrate', ['--force' => true])` داخل web routes. ده خطر حتى لو فيه auth check يدوي — أحسن تحوله لـ Artisan command أو تمسحه من الـ routes تماماً.

### Category full_path بيعمل N+1 على نفسه
*   **الموقع:** `Category.php → getFullPathAttribute`
*   **الوصف:** الـ `full_path` accessor بيعمل recursive call على `$this->parent` من غير eager loading. لو عندك شجرة categories بعمق 3، كل call على `full_path` هيعمل query لكل level.

### UserController بيبعت displayName بدل display_name
*   **الموقع:** `UserController.php → storeUser / updateUser`
*   **الوصف:** `$validated['displayName'] = $validated['name']` — الـ column في الـ migration اسمه `display_name` (snake_case). Laravel بيعمل auto-save لـ camelCase أحياناً لكن ده غير safe وممكن يتجاهل على بعض DB drivers.

### README لسه default Laravel readme
*   **الموقع:** `README.md`
*   **الوصف:** الـ README لسه بيتكلم عن Laravel framework بشكل عام وملوش علاقة بـ GenShelf. لو هتنشر المشروع أو تحطه على GitHub، المفروض تكتب README خاص بيه: setup steps، default credentials، modules.
