# Student Management and Grade Analysis System using PHP and MySQL.

    First project in IT 223 – Interactive Programming and Technologies I.

## Features

1.Add New Student
2.Display All Student   
3.View Individual Student Record
4.Search Functionality
5.Sort Functionality
6.Edit Student Information
7.Delete Student Record

## Setup

1. Ensure XAMPP is installed and running (Apache and MySQL).
2. Place this project in `C:\xampp\htdocs\SAMPLE CRUD(item)\`
3. The database `crud_sample` and table `items` are already created.
4. Open your browser and go to `http://localhost/SAMPLE%20CRUD(item)/`

## Files

- `index.php`: Main page with item list and add form
- `db.php`: Database connection
- `add_to_cart.php`: Handle adding items to cart
- `cart.php`: View cart
- `remove_from_cart.php`: Remove items from cart
- `edit.php`: Edit item page
- `delete.php`: Delete item

## Usage


- Use the "Add New Item" button to add items.
- Click "Add" on an item card to add it to cart.
- Go to Cart to view and remove items.
- Use Edit/Delete buttons for CRUD operations.

Note: Cart is session-based, so it resets when session ends.

## Admin login
-admin
-admin