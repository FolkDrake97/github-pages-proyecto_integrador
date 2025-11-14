class AcademicPlatform {
    constructor() {
        this.apiBase = '../backend/api';
        this.currentUser = null;
        this.init();
    }

    init() {
        this.loadUserData();
        this.setupNavigation();
        this.showLogin();
    }

    loadUserData() {
        const userData = localStorage.getItem('userData');
        if (userData) {
            this.currentUser = JSON.parse(userData);
        }
    }

    setupNavigation() {
        // Navigation global setup
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action]')) {
                const action = e.target.getAttribute('data-action');
                this.executeAction(action);
            }
        });
    }

    executeAction(action) {
        switch(action) {
            case 'logout':
                this.logout();
                break;
            case 'showDashboard':
                this.showDashboard();
                break;
            case 'showSubjects':
                this.showSubjects();
                break;
            case 'showMySubjects':
                this.showMySubjects();
                break;
            case 'showTasks':
                this.showTasks();
                break;
            case 'showGrades':
                this.showGrades();
                break;
            case 'showReports':
                this.showReports();
                break;
            case 'showManageSubjects':
                this.showManageSubjects();
                break;
            case 'showPendingRequests':
                this.showPendingRequests();
                break;
        }
    }

    showLogin() {
        document.getElementById('app').innerHTML = `
            <div class="login-container">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="card mt-5">
                            <div class="card-header bg-primary text-white">
                                <h4 class="text-center mb-0">Iniciar Sesión</h4>
                            </div>
                            <div class="card-body">
                                <form id="loginForm">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                                </form>
                                <div class="text-center mt-3">
                                    <a href="#" id="showRegister">¿No tienes cuenta? Regístrate</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Event listeners para login
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        document.getElementById('showRegister').addEventListener('click', (e) => {
            e.preventDefault();
            this.showRegister();
        });
    }

    showRegister() {
        document.getElementById('app').innerHTML = `
            <div class="login-container">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="card mt-5">
                            <div class="card-header bg-success text-white">
                                <h4 class="text-center mb-0">Registro de Estudiante</h4>
                            </div>
                            <div class="card-body">
                                <form id="registerForm">
                                    <div class="mb-3">
                                        <label for="regName" class="form-label">Nombre Completo</label>
                                        <input type="text" class="form-control" id="regName" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="regEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="regEmail" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="regPassword" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="regPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="regConfirmPassword" class="form-label">Confirmar Contraseña</label>
                                        <input type="password" class="form-control" id="regConfirmPassword" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Registrarse</button>
                                </form>
                                <div class="text-center mt-3">
                                    <a href="#" id="showLogin">¿Ya tienes cuenta? Inicia Sesión</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('registerForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegister();
        });

        document.getElementById('showLogin').addEventListener('click', (e) => {
            e.preventDefault();
            this.showLogin();
        });
    }

    async handleLogin() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            const response = await fetch(`${this.apiBase}/auth.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'login',
                    email: email,
                    password: password
                })
            });

            const data = await response.json();
            
            if (data.success) {
                localStorage.setItem('userData', JSON.stringify(data.user));
                this.currentUser = data.user;
                this.showDashboard();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión con el servidor');
        }
    }

    async handleRegister() {
        const name = document.getElementById('regName').value;
        const email = document.getElementById('regEmail').value;
        const password = document.getElementById('regPassword').value;
        const confirmPassword = document.getElementById('regConfirmPassword').value;

        if (password !== confirmPassword) {
            this.showError('Las contraseñas no coinciden');
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/auth.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'register',
                    nombre: name,
                    email: email,
                    password: password
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Registro exitoso. Ahora puedes iniciar sesión.');
                this.showLogin();
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión con el servidor');
        }
    }

    showDashboard() {
        if (!this.currentUser) {
            this.showLogin();
            return;
        }

        let dashboardContent = '';
        
        switch(this.currentUser.rol) {
            case 'estudiante':
                dashboardContent = this.getStudentDashboard();
                break;
            case 'maestro':
                dashboardContent = this.getTeacherDashboard();
                break;
            case 'administrador':
                dashboardContent = this.getAdminDashboard();
                break;
            default:
                dashboardContent = '<p>Rol no reconocido</p>';
        }

        document.getElementById('app').innerHTML = `
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container">
                    <a class="navbar-brand" href="#" data-action="showDashboard">Plataforma Académica</a>
                    <div class="navbar-nav ms-auto">
                        <span class="navbar-text me-3">Hola, ${this.currentUser.nombre}</span>
                        <button class="btn btn-outline-light btn-sm" data-action="logout">Cerrar Sesión</button>
                    </div>
                </div>
            </nav>
            <div class="container mt-4">
                ${dashboardContent}
            </div>
        `;

        // Cargar datos del dashboard
        this.loadDashboardData();
    }

    getStudentDashboard() {
        return `
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Materias Inscritas</h5>
                            <p class="card-text display-6" id="subjectCount">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Tareas Pendientes</h5>
                            <p class="card-text display-6" id="pendingTasks">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Promedio General</h5>
                            <p class="card-text display-6" id="averageGrade">0.0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Solicitudes</h5>
                            <p class="card-text display-6" id="enrollmentStatus">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <button class="btn btn-primary me-2" data-action="showSubjects">Ver Materias Disponibles</button>
                    <button class="btn btn-secondary me-2" data-action="showMySubjects">Mis Materias</button>
                    <button class="btn btn-info me-2" data-action="showTasks">Mis Tareas</button>
                    <button class="btn btn-success me-2" data-action="showGrades">Mis Calificaciones</button>
                    <button class="btn btn-warning" data-action="showReports">Reportes</button>
                </div>
            </div>
        `;
    }

    getTeacherDashboard() {
        return `
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Mis Materias</h5>
                            <p class="card-text display-6" id="teacherSubjects">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Solicitudes Pendientes</h5>
                            <p class="card-text display-6" id="pendingRequests">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Estudiantes Totales</h5>
                            <p class="card-text display-6" id="totalStudents">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <button class="btn btn-primary me-2" data-action="showManageSubjects">Gestionar Materias</button>
                    <button class="btn btn-warning me-2" data-action="showPendingRequests">Solicitudes Pendientes</button>
                    <button class="btn btn-info me-2" data-action="showReports">Ver Reportes</button>
                </div>
            </div>
        `;
    }

    getAdminDashboard() {
        return `
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Usuarios</h5>
                            <p class="card-text display-6" id="totalUsers">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Materias</h5>
                            <p class="card-text display-6" id="totalSubjects">0</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <button class="btn btn-primary me-2" data-action="showManageSubjects">Gestionar Materias</button>
                    <button class="btn btn-info me-2" data-action="showReports">Ver Reportes</button>
                </div>
            </div>
        `;
    }

    async loadDashboardData() {
        if (!this.currentUser) return;

        try {
            switch(this.currentUser.rol) {
                case 'estudiante':
                    await this.loadStudentDashboardData();
                    break;
                case 'maestro':
                    await this.loadTeacherDashboardData();
                    break;
                case 'administrador':
                    await this.loadAdminDashboardData();
                    break;
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    async loadStudentDashboardData() {
        // Cargar conteo de materias inscritas
        const enrollmentsResponse = await fetch(`${this.apiBase}/enrollments.php?student_id=${this.currentUser.id}`);
        const enrollmentsData = await enrollmentsResponse.json();
        
        if (enrollmentsData.success) {
            const approvedEnrollments = enrollmentsData.data.filter(e => e.estado === 'aprobada');
            document.getElementById('subjectCount').textContent = approvedEnrollments.length;
            document.getElementById('enrollmentStatus').textContent = enrollmentsData.data.length;
        }

        // Cargar tareas pendientes
        const tasksResponse = await fetch(`${this.apiBase}/tasks.php?student_id=${this.currentUser.id}`);
        const tasksData = await tasksResponse.json();
        
        if (tasksData.success) {
            document.getElementById('pendingTasks').textContent = tasksData.data.length;
        }

        // Cargar promedio general
        const gradesResponse = await fetch(`${this.apiBase}/grades.php?student_id=${this.currentUser.id}`);
        const gradesData = await gradesResponse.json();
        
        if (gradesData.success) {
            document.getElementById('averageGrade').textContent = gradesData.average ? gradesData.average.toFixed(2) : '0.0';
        }
    }

    async loadTeacherDashboardData() {
        // Cargar materias del maestro
        const subjectsResponse = await fetch(`${this.apiBase}/subjects.php?teacher_id=${this.currentUser.id}`);
        const subjectsData = await subjectsResponse.json();
        
        if (subjectsData.success) {
            document.getElementById('teacherSubjects').textContent = subjectsData.data.length;
        }

        // Cargar solicitudes pendientes
        const requestsResponse = await fetch(`${this.apiBase}/enrollments.php?teacher_id=${this.currentUser.id}`);
        const requestsData = await requestsResponse.json();
        
        if (requestsData.success) {
            document.getElementById('pendingRequests').textContent = requestsData.data.length;
        }
    }

    async showSubjects() {
        try {
            const response = await fetch(`${this.apiBase}/subjects.php`);
            const data = await response.json();
            
            let subjectsHtml = '';
            
            if (data.success && data.data.length > 0) {
                subjectsHtml = data.data.map(subject => `
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">${subject.nombre}</h5>
                                <p class="card-text">${subject.descripcion || 'Sin descripción'}</p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <strong>Créditos:</strong> ${subject.creditos}<br>
                                        <strong>Maestro:</strong> ${subject.maestro || 'No asignado'}
                                    </small>
                                </p>
                            </div>
                            <div class="card-footer">
                                <button class="btn btn-primary btn-sm" onclick="platform.requestEnrollment(${subject.id})">
                                    Solicitar Inscripción
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                subjectsHtml = '<div class="col-12"><p class="text-center">No hay materias disponibles</p></div>';
            }
            
            this.renderPage(`
                <h2 class="mb-4">Materias Disponibles</h2>
                <div class="row">
                    ${subjectsHtml}
                </div>
            `);
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error al cargar las materias');
        }
    }

    async requestEnrollment(subjectId) {
        if (!this.currentUser) {
            this.showError('Debes iniciar sesión primero');
            return;
        }

        try {
            const response = await fetch(`${this.apiBase}/enrollments.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_estudiante: this.currentUser.id,
                    id_materia: subjectId
                })
            });

            const data = await response.json();
            this.showMessage(data.message, data.success ? 'success' : 'error');
            
            if (data.success) {
                setTimeout(() => this.showSubjects(), 1500);
            }
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error al enviar la solicitud');
        }
    }

    // Métodos de utilidad
    renderPage(content) {
        document.getElementById('app').innerHTML = `
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container">
                    <a class="navbar-brand" href="#" data-action="showDashboard">Plataforma Académica</a>
                    <div class="navbar-nav ms-auto">
                        <span class="navbar-text me-3">Hola, ${this.currentUser.nombre}</span>
                        <button class="btn btn-outline-light btn-sm" data-action="logout">Cerrar Sesión</button>
                    </div>
                </div>
            </nav>
            <div class="container mt-4">
                ${content}
            </div>
        `;
    }

    showError(message) {
        this.showMessage(message, 'error');
    }

    showSuccess(message) {
        this.showMessage(message, 'success');
    }

    showMessage(message, type = 'info') {
        // Crear alerta Bootstrap
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'success' ? 'alert-success' : 'alert-info';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insertar al inicio del contenedor app
        const app = document.getElementById('app');
        app.insertBefore(alertDiv, app.firstChild);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    logout() {
        localStorage.removeItem('userData');
        this.currentUser = null;
        this.showLogin();
    }

    // Placeholder para otros métodos que necesites implementar
    showMySubjects() {
        this.renderPage('<h2>Mis Materias - En desarrollo</h2>');
    }

    showTasks() {
        this.renderPage('<h2>Mis Tareas - En desarrollo</h2>');
    }

    showGrades() {
        this.renderPage('<h2>Mis Calificaciones - En desarrollo</h2>');
    }

    showReports() {
        this.renderPage('<h2>Reportes - En desarrollo</h2>');
    }

    showManageSubjects() {
        this.renderPage('<h2>Gestionar Materias - En desarrollo</h2>');
    }

    showPendingRequests() {
        this.renderPage('<h2>Solicitudes Pendientes - En desarrollo</h2>');
    }
}

// Inicializar la aplicación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.platform = new AcademicPlatform();
});