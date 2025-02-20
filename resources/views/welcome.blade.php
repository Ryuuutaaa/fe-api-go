<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">User Management System</h2>
        <div class="card">
            <div class="card-header bg-primary text-white">User Form</div>
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
            <div class="card-header bg-secondary text-white">Users Table</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <!-- User data will be inserted here -->
                        </tbody>
                    </table>
                    <div id="pagination" class="d-flex justify-content-center mt-3">
                        <!-- Pagination controls will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
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

    <!-- Delete Confirmation Modal -->
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
            // Initialize modals
            const alertModal = new bootstrap.Modal(document.getElementById('alertModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            let currentDeleteId = null;
            let editMode = false;

            // Pagination variables
            let currentPage = 1;
            let itemsPerPage = 5;
            let allUsers = [];

            // Initial load of users
            fetchUsers();

            // Form submission handler
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const name = $('#name').val();
                const email = $('#email').val();
                const userId = $('#userId').val();

                if (editMode) {
                    updateUser(userId, name, email);
                } else {
                    createUser(name, email);
                }
            });

            // Edit button click handler (delegated event)
            $(document).on('click', '.edit-btn', function() {
                const userId = $(this).data('id');
                const name = $(this).data('name');
                const email = $(this).data('email');

                // Fill the form with user data
                $('#userId').val(userId);
                $('#name').val(name);
                $('#email').val(email);

                // Change form state
                $('#submitBtn').text('Update');
                $('#cancelBtn').show();
                editMode = true;
            });

            // Delete button click handler (delegated event)
            $(document).on('click', '.delete-btn', function() {
                currentDeleteId = $(this).data('id');
                deleteModal.show();
            });

            // Cancel button click handler
            $('#cancelBtn').on('click', function() {
                resetForm();
            });

            // Confirm delete button click handler
            $('#confirmDelete').on('click', function() {
                if (currentDeleteId) {
                    deleteUser(currentDeleteId);
                    deleteModal.hide();
                }
            });

            // Pagination click handler
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                const targetPage = $(this).data('page');
                if (targetPage !== currentPage) {
                    currentPage = targetPage;
                    renderUserTable();
                }
            });

            // Clear validation errors
            function clearErrors() {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            // Reset form to initial state
            function resetForm() {
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#submitBtn').text('Submit');
                $('#cancelBtn').hide();
                clearErrors();
                editMode = false;
            }

            // Show error in form fields
            function showValidationErrors(errors) {
                if (typeof errors === 'object') {
                    for (let field in errors) {
                        if (errors.hasOwnProperty(field)) {
                            const errorMessage = errors[field][0];
                            const inputField = $('#' + field);

                            inputField.addClass('is-invalid');
                            inputField.after(
                                `<div class="invalid-feedback">${errorMessage}</div>`
                            );
                        }
                    }
                }
            }

            // Show alert modal
            function showAlert(title, message, isSuccess = false) {
                $('#alertTitle').text(title);
                if (isSuccess) {
                    $('#alertTitle').css('color', 'green');
                } else {
                    $('#alertTitle').css('color', 'red');
                }
                $('#alertMessage').text(message);
                alertModal.show();
            }

            // Fetch all users
            function fetchUsers() {
                $.ajax({
                    url: "{{ route('get.all.users') }}",
                    type: "GET",
                    success: function(response) {
                        if (response.status) {
                            // Store all users for pagination
                            if (response.data && response.data.data) {
                                allUsers = response.data.data;
                                currentPage = 1;
                                renderUserTable();
                            } else {
                                showAlert('Error', 'Invalid response format');
                            }
                        } else {
                            showAlert('Error', 'Failed to fetch users');
                        }
                    },
                    error: function(xhr) {
                        showAlert('Error', 'An error occurred while fetching data');
                    }
                });
            }

            // Generate pagination controls
            function generatePagination() {
                const totalPages = Math.ceil(allUsers.length / itemsPerPage);
                let paginationHTML = '<nav aria-label="Page navigation"><ul class="pagination">';

                // Previous button
                paginationHTML += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                                    <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                   </li>`;

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                                       </li>`;
                }

                // Next button
                paginationHTML += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                                    <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                   </li>`;

                paginationHTML += '</ul></nav>';

                $('#pagination').html(paginationHTML);
            }

            // Render user table with pagination
            function renderUserTable() {
                $('#userTableBody').empty();

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, allUsers.length);

                if (allUsers.length > 0) {
                    // Using traditional for loop instead of forEach
                    for (let i = startIndex; i < endIndex; i++) {
                        const user = allUsers[i];
                        if (user && user.ID && user.Name && user.Email) {
                            let tr = document.createElement('tr');

                            let tdId = document.createElement('td');
                            tdId.textContent = user.ID || 'N/A';

                            let tdName = document.createElement('td');
                            tdName.textContent = user.Name;

                            let tdEmail = document.createElement('td');
                            tdEmail.textContent = user.Email;

                            let tdActions = document.createElement('td');

                            let editBtn = document.createElement('button');
                            editBtn.setAttribute('type', 'button');
                            editBtn.setAttribute('class', 'btn btn-sm btn-primary edit-btn me-2');
                            editBtn.setAttribute('data-id', user.ID);
                            editBtn.setAttribute('data-name', user.Name);
                            editBtn.setAttribute('data-email', user.Email);
                            editBtn.textContent = 'Edit';

                            let deleteBtn = document.createElement('button');
                            deleteBtn.setAttribute('type', 'button');
                            deleteBtn.setAttribute('class', 'btn btn-sm btn-danger delete-btn');
                            deleteBtn.setAttribute('data-id', user.ID);
                            deleteBtn.textContent = 'Delete';

                            tdActions.appendChild(editBtn);
                            tdActions.appendChild(deleteBtn);

                            tr.appendChild(tdId);
                            tr.appendChild(tdName);
                            tr.appendChild(tdEmail);
                            tr.appendChild(tdActions);

                            document.getElementById('userTableBody').appendChild(tr);
                        }
                    }
                } else {
                    let tr = document.createElement('tr');
                    let td = document.createElement('td');
                    td.setAttribute('colspan', '4');
                    td.setAttribute('class', 'text-center');
                    td.textContent = 'No users found';
                    tr.appendChild(td);
                    document.getElementById('userTableBody').appendChild(tr);
                }

                // Generate pagination
                generatePagination();
            }

            // Create new user
            function createUser(name, email) {
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
                            showAlert('Success', response.message, true);
                            fetchUsers(); // Refresh the table
                            resetForm();
                        } else {
                            showAlert('Error', 'Failed to create user');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.error) {
                            showValidationErrors(response.error);
                        } else {
                            showAlert('Error', 'An unexpected error occurred');
                        }
                    }
                });
            }

            // Update existing user
            function updateUser(id, name, email) {
                $.ajax({
                    url: "{{ route('update.user', '') }}/" + id,
                    type: "PUT",
                    data: {
                        name: name,
                        email: email,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            showAlert('Success', response.message, true);
                            fetchUsers(); // Refresh the table
                            resetForm();
                        } else {
                            showAlert('Error', 'Failed to update user');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (response && response.error) {
                            showValidationErrors(response.error);
                        } else {
                            showAlert('Error', 'An unexpected error occurred');
                        }
                    }
                });
            }

            // Delete user
            function deleteUser(id) {
                $.ajax({
                    url: "{{ route('delete.user', '') }}/" + id,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            showAlert('Success', response.message, true);
                            fetchUsers(); // Refresh the table
                        } else {
                            showAlert('Error', 'Failed to delete user');
                        }
                    },
                    error: function(xhr) {
                        showAlert('Error', 'An error occurred while deleting the user');
                    }
                });
            }
        });
    </script>
</body>

</html>
