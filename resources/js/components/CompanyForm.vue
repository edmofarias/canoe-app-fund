<template>
  <div class="company-form">
    <h2>Create Company</h2>
    
    <form @submit.prevent="submitForm" class="form">
      <div v-if="formError" class="form-error">
        {{ formError }}
      </div>
      
      <div class="form-group">
        <label for="name">Company Name *</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          placeholder="Enter company name"
          :class="{ 'input-error': errors.name }"
        />
        <span v-if="errors.name" class="error-message">{{ errors.name[0] }}</span>
      </div>
      
      <div class="form-actions">
        <button type="button" @click="goBack" class="btn-cancel">Cancel</button>
        <button type="submit" :disabled="submitting" class="btn-submit">
          {{ submitting ? 'Creating...' : 'Create Company' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { companyApi } from '../api.js';

export default {
  name: 'CompanyForm',
  data() {
    return {
      form: {
        name: ''
      },
      submitting: false,
      formError: null,
      errors: {}
    };
  },
  methods: {
    async submitForm() {
      this.submitting = true;
      this.formError = null;
      this.errors = {};
      
      try {
        await companyApi.create(this.form);
        
        // Navigate back to companies list
        this.$router.push({ name: 'company-list' });
      } catch (err) {
        if (err.response?.status === 422) {
          // Validation errors
          this.errors = err.response.data.errors || {};
          this.formError = err.response.data.message || 'Validation failed';
        } else {
          this.formError = err.response?.data?.message || 'Failed to create company';
        }
        console.error('Error submitting form:', err);
      } finally {
        this.submitting = false;
      }
    },
    
    goBack() {
      this.$router.push({ name: 'company-list' });
    }
  }
}
</script>

<style scoped>
.company-form {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  max-width: 600px;
  margin: 0 auto;
}

.form {
  margin-top: 1.5rem;
}

.form-error {
  background-color: #ffebee;
  color: #c62828;
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #333;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.form-group input:focus {
  outline: none;
  border-color: #1976d2;
}

.input-error {
  border-color: #d32f2f !important;
}

.error-message {
  display: block;
  color: #d32f2f;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 2rem;
}

.btn-cancel,
.btn-submit {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-cancel {
  background-color: #757575;
  color: white;
}

.btn-cancel:hover {
  background-color: #616161;
}

.btn-submit {
  background-color: #1976d2;
  color: white;
}

.btn-submit:hover:not(:disabled) {
  background-color: #1565c0;
}

.btn-submit:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>
