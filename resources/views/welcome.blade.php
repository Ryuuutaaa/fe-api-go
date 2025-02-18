<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form dengan Bootstrap 5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">Form</div>
            <div class="card-body">
                <form id="userForm">
                    @csrf
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Enter your name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter your email">
                    </div>
                    <button type="submit" id="submitBtn" class="btn btn-primary">Submit</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary"
                        style="display: none;">Cancel</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">Tabel</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <!-- User data will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertTitle">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="alertMessage">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let users = [];
            const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

            function clearErrors() {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            $('#userForm').on('submit', function(e) {
                e.preventDefault();

                clearErrors();

                const name = $('#name').val();
                const email = $('#email').val();

                $.ajax({
                    url: "{{ route('create.user') }}",
                    type: "POST",
                    data: {
                        name: name,
                        email: email,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            users.push(response.data);

                            $('#alertTitle').text('Success').css('color', 'green');
                            $('#alertMessage').text(response.message);
                            alertModal.show();

                            renderUserTable();

                            resetForm();
                        } else {
                            $('#alertTitle').text('Error');
                            $('#alertMessage').text('Failed to create user');
                            alertModal.show();
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;

                        if (response && response.error) {
                            if (typeof response.error === 'object') {
                                Object.keys(response.error).forEach(function(field) {
                                    const errorMessage = response.error[field][0];
                                    const inputField = $('#' + field);

                                    inputField.addClass('is-invalid');
                                    inputField.after(
                                        `<div class="invalid-feedback">${errorMessage}</div>`
                                    );
                                });
                            } else {
                                $('#alertTitle').text('Error');
                                $('#alertMessage').text(response.error);
                                alertModal.show();
                            }
                        } else {
                            $('#alertTitle').text('Error');
                            $('#alertMessage').text('An unexpected error occurred');
                            alertModal.show();
                        }
                    }
                });
            });

            window.deleteUser = function(id) {
                $('#deleteModal').modal('show');

                $('#confirmDelete').off('click').on('click', function() {
                    $.ajax({
                        url: "{{ route('delete.user', '') }}/" + id,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#alertTitle').text('Success');
                                $('#alertMessage').text(response.message);
                                alertModal.show();
                                fetchUser();
                            } else {
                                $('#alertTitle').text('Error');
                                $('#alertMessage').text(response.error ||
                                    'Failed to delete user');
                                alertModal.show();
                            }
                        },
                        error: function(xhr) {
                            console.error('Error deleting user:', xhr.responseJSON);
                            $('#alertTitle').text('Error');
                            $('#alertMessage').text(xhr.responseJSON?.error ||
                                'An error occurred while deleting data');
                            alertModal.show();
                        }
                    });

                    $('#deleteModal').modal('hide');
                });
            };

            function fetchUser() {
                $.ajax({
                    url: "{{ route('get.all.users') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.status) {
                            $('#userTableBody').html('');
                            response.data.data.forEach(function(user) {
                                $('#userTableBody').append(`
                        <tr>
                            <td>${user.Name}</td>
                            <td>${user.Email}</td>
                            <td>
                                <button type="button" class="btn btn-primary" onclick="editUser(${user.id})">Edit</button>
                                <button type="button" class="btn btn-danger" onclick="deleteUser(${user.id})">Delete</button>
                            </td>
                        </tr>
                    `);
                            });
                        } else {
                            $('#alertTitle').text('Error');
                            $('#alertMessage').text('Failed to fetch users');
                            alertModal.show();
                        }
                    },
                    error: function(xhr) {
                        $('#alertTitle').text('Error');
                        $('#alertMessage').text('An error occurred while fetching data');
                        alertModal.show();
                    }
                });
            }

            fetchUser();
        });
    </script>
</body>

</html>
