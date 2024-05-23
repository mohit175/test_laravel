<!DOCTYPE html>
<html lang="en">

<head>
    <title>User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <h1>User</h1>
        <div id="user-form">
            <form id="userForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="userId" name="userId">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <div>
                        <input type="radio" id="male" name="gender" value="male" required>
                        <label for="male">Male</label>
                        <input type="radio" id="female" name="gender" value="female" required>
                        <label for="female">Female</label>
                        <input type="radio" id="other" name="gender" value="other" required>
                        <label for="other">Other</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control" id="image" name="image">
                </div>
                <div class="mb-3">
                    <label for="file" class="form-label">File</label>
                    <input type="file" class="form-control" id="file" name="file">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

        <div class="mt-5">
            <h2>Users</h2>
            <div>
                <label for="searchName" class="form-label">Name</label>
                <input type="text" id="searchName">
                <label for="searchEmail" class="form-label">Email</label>
                <input type="text" id="searchEmail">
                <label for="searchGender" class="form-label">Gender</label>
                <select id="searchGender">
                    <option value="">All</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <button id="searchButton">Search</button>
            </div>
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Image</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    <!-- Users will be appended here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            fetchUsers();

            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let userId = $('#userId').val();
                //UPDATE USER DETAILS
                if (userId) {
                    formData.append('_method', 'PUT');
                    $.ajax({
                        url: `/users/${userId}`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#userForm')[0].reset();
                            $('#userId').val('');
                            fetchUsers();
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            alert('An error occurred while updating the user.');
                        }

                    });
                } else {
                    //NEW DATA
                    $.ajax({
                        url: `/users`,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#userForm')[0].reset();
                            fetchUsers();
                        },
                        error: function(xhr, status, error) {
                            let errorMessage = xhr.responseJSON ? xhr.responseJSON.message :
                                'An error occurred while updating the user.';
                            alert('Error: ' + errorMessage);

                        }


                    });
                }
            });

            function fetchUsers() {
                $.ajax({
                    url: `/get-users`,
                    type: 'GET',
                    success: function(response) {
                        let userRows = '';
                        response.forEach(user => {
                            userRows += `<tr>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${user.phone}</td>
                                <td>${user.gender}</td>
                                <td><img src="storage/${user.image}" width="50"></td>
                                <td><a href="storage/${user.file}" target="_blank">File</a></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="editUser(${user.id})">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">Delete</button>
                                </td>
                            </tr>`;
                        });
                        $('#userTableBody').html(userRows);
                    }
                });
            }

            window.editUser = function(id) {
                $.ajax({
                    url: `/users/${id}`,
                    type: 'GET',
                    success: function(response) {
                        $('#userId').val(response.id);
                        $('#name').val(response.name);
                        $('#email').val(response.email);
                        $('#phone').val(response.phone);
                        $(`input[name="gender"][value="${response.gender}"]`).prop('checked', true);
                    }
                });
            };

            window.deleteUser = function(id) {
                if (confirm('Are you sure?')) {
                    $.ajax({
                        url: `/users/${id}`,
                        type: 'DELETE',
                        data: {
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            fetchUsers();
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                            alert('An error occurred while searching for users.');
                        }

                    });
                }
            };

            $('#searchButton').on('click', function() {
                let name = $('#searchName').val();
                let email = $('#searchEmail').val();
                let gender = $('#searchGender').val();

                $.ajax({
                    url: `/get-users?name=${name}&email=${email}&gender=${gender}`,
                    type: 'GET',
                    success: function(response) {
                        let userRows = '';
                        response.forEach(user => {
                            userRows += `<tr>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${user.phone}</td>
                                <td>${user.gender}</td>
                                <td><img src="storage/${user.image}" width="50"></td>
                                <td><a href="storage/${user.file}" target="_blank">File</a></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="editUser(${user.id})">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.id})">Delete</button>
                                </td>
                            </tr>`;
                        });
                        $('#userTableBody').html(userRows);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        alert('An error occurred while searching for users.');
                    }

                });
            });
        });
    </script>
</body>

</html>
