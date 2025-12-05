function generateCaptcha() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let captcha = '';
    for (let i = 0; i < 6; i++) {
        captcha += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return captcha;
}

function displayCaptcha() {
    const captchaText = generateCaptcha();
    document.getElementById('captcha-display').textContent = captchaText;
    document.getElementById('captcha-value').value = captchaText;
}

function validateCaptcha() {
    const userInput = document.getElementById('captcha-input').value;
    const captchaValue = document.getElementById('captcha-value').value;
    
    if (userInput !== captchaValue) {
        alert('CAPTCHA incorrect. Veuillez réessayer.');
        displayCaptcha();
        document.getElementById('captcha-input').value = '';
        return false;
    }
    return true;
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    for (let input of inputs) {
        if (!input.value.trim()) {
            alert('Veuillez remplir tous les champs obligatoires.');
            input.focus();
            return false;
        }
    }
    return true;
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 8 && 
           /[A-Z]/.test(password) && 
           /[a-z]/.test(password) && 
           /[0-9]/.test(password);
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    alert('Lien copié dans le presse-papier !');
}

function confirmAction(message) {
    return confirm(message);
}

function addQuestion() {
    const container = document.getElementById('questions-container');
    const questionCount = container.children.length + 1;
    
    const questionBlock = document.createElement('div');
    questionBlock.className = 'question-block fade-in';
    questionBlock.innerHTML = `
        <div class="question-header">
            <h4>Question ${questionCount}</h4>
            <button type="button" class="btn btn-danger" onclick="removeQuestion(this)">Supprimer</button>
        </div>
        <div class="form-group">
            <label class="form-label">Texte de la question</label>
            <input type="text" name="questions[${questionCount}][text]" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">Type de question</label>
            <select name="questions[${questionCount}][type]" class="form-select" onchange="handleQuestionTypeChange(this, ${questionCount})">
                <option value="mcq">QCM</option>
                <option value="free_text">Réponse libre</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Points</label>
            <input type="number" name="questions[${questionCount}][points]" class="form-control" value="1" min="0">
        </div>
        <div id="answers-${questionCount}" class="answers-container">
            <label class="form-label">Réponses</label>
            <div class="answer-item">
                <input type="text" name="questions[${questionCount}][answers][0]" class="form-control" placeholder="Réponse 1" required>
                <label>
                    <input type="radio" name="questions[${questionCount}][correct]" value="0" required> Correcte
                </label>
            </div>
        </div>
        <button type="button" class="btn btn-outline" onclick="addAnswer(${questionCount})">Ajouter une réponse</button>
    `;
    
    container.appendChild(questionBlock);
}

function removeQuestion(button) {
    if (confirmAction('Êtes-vous sûr de vouloir supprimer cette question ?')) {
        button.closest('.question-block').remove();
        updateQuestionNumbers();
    }
}

function updateQuestionNumbers() {
    const questions = document.querySelectorAll('.question-block');
    questions.forEach((question, index) => {
        question.querySelector('h4').textContent = `Question ${index + 1}`;
    });
}

function addAnswer(questionNumber) {
    const container = document.getElementById(`answers-${questionNumber}`);
    const answerCount = container.querySelectorAll('.answer-item').length;
    
    const answerItem = document.createElement('div');
    answerItem.className = 'answer-item fade-in';
    answerItem.style.marginTop = '0.5rem';
    answerItem.innerHTML = `
        <input type="text" name="questions[${questionNumber}][answers][${answerCount}]" class="form-control" placeholder="Réponse ${answerCount + 1}" required>
        <label>
            <input type="radio" name="questions[${questionNumber}][correct]" value="${answerCount}" required> Correcte
        </label>
        <button type="button" class="btn btn-danger" onclick="removeAnswer(this)">×</button>
    `;
    
    container.appendChild(answerItem);
}

function removeAnswer(button) {
    button.closest('.answer-item').remove();
}

function handleQuestionTypeChange(select, questionNumber) {
    const answersContainer = document.getElementById(`answers-${questionNumber}`);
    const addAnswerBtn = select.closest('.question-block').querySelector('button[onclick*="addAnswer"]');
    
    if (select.value === 'free_text') {
        answersContainer.style.display = 'none';
        addAnswerBtn.style.display = 'none';
    } else {
        answersContainer.style.display = 'block';
        addAnswerBtn.style.display = 'inline-block';
    }
}

function toggleStatus(type, id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const message = `Êtes-vous sûr de vouloir ${newStatus === 'active' ? 'activer' : 'désactiver'} cet élément ?`;
    
    if (confirmAction(message)) {
        window.location.href = `?action=toggle&type=${type}&id=${id}&status=${newStatus}`;
    }
}

function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            if (cells[j]) {
                const txtValue = cells[j].textContent || cells[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        rows[i].style.display = found ? '' : 'none';
    }
}

function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const rows = Array.from(table.getElementsByTagName('tr')).slice(1);
    const isAscending = table.getAttribute('data-sort-order') !== 'asc';
    
    rows.sort((a, b) => {
        const aText = a.getElementsByTagName('td')[columnIndex].textContent;
        const bText = b.getElementsByTagName('td')[columnIndex].textContent;
        
        return isAscending ? 
            aText.localeCompare(bText) : 
            bText.localeCompare(aText);
    });
    
    rows.forEach(row => table.appendChild(row));
    table.setAttribute('data-sort-order', isAscending ? 'asc' : 'desc');
}

function showLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading';
    loading.id = 'loading-overlay';
    loading.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-overlay');
    if (loading) {
        loading.remove();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form.id)) {
                e.preventDefault();
            }
        });
    });
    
    const captchaDisplay = document.getElementById('captcha-display');
    if (captchaDisplay) {
        displayCaptcha();
    }
    
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
        }, index * 100);
    });
});

window.addEventListener('load', function() {
    hideLoading();
});