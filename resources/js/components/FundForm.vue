<template>
  <div class="fund-form">
    <h2>{{ isEdit ? 'Edit Fund' : 'Create Fund' }}</h2>
    
    <div v-if="loading" class="loading">Loading...</div>
    
    <form v-else @submit.prevent="submitForm" class="form">
      <div v-if="formError" class="form-error">
        {{ formError }}
      </div>
      
      <div class="form-group">
        <label for="name">Fund Name *</label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          :class="{ 'input-error': errors.name }"
        />
        <span v-if="errors.name" class="error-message">{{ errors.name[0] }}</span>
      </div>
      
      <div class="form-group">
        <label for="start_year">Start Year *</label>
        <input
          id="start_year"
          v-model.number="form.start_year"
          type="number"
          required
          min="1900"
          :max="new Date().getFullYear() + 10"
          :class="{ 'input-error': errors.start_year }"
        />
        <span v-if="errors.start_year" class="error-message">{{ errors.start_year[0] }}</span>
      </div>
      
      <div class="form-group">
        <label for="fund_manager_id">Fund Manager *</label>
        <select
          id="fund_manager_id"
          v-model.number="form.fund_manager_id"
          required
          :class="{ 'input-error': errors.fund_manager_id }"
        >
          <option value="">Select a fund manager</option>
          <option v-for="manager in fundManagers" :key="manager.id" :value="manager.id">
            {{ manager.name }}
          </option>
        </select>
        <span v-if="errors.fund_manager_id" class="error-message">{{ errors.fund_manager_id[0] }}</span>
      </div>
      
      <div class="form-group">
        <label>Aliases</label>
        <div v-for="(alias, index) in form.aliases" :key="index" class="alias-input">
          <input
            v-model="form.aliases[index]"
            type="text"
            placeholder="Enter alias name"
            :class="{ 'input-error': errors[`aliases.${index}`] }"
          />
          <button type="button" @click="removeAlias(index)" class="btn-remove">Remove</button>
        </div>
        <button type="button" @click="addAlias" class="btn-add">Add Alias</button>
        <span v-if="errors.aliases" class="error-message">{{ errors.aliases[0] }}</span>
      </div>
      
      <div class="form-group">
        <label for="company_ids">Companies</label>
        <select
          id="company_ids"
          v-model="form.company_ids"
          multiple
          size="5"
          :class="{ 'input-error': errors.company_ids }"
        >
          <option v-for="company in companies" :key="company.id" :value="company.id">
            {{ company.name }}
          </option>
        </select>
        <p class="help-text">Hold Ctrl (Cmd on Mac) to select multiple companies</p>
        <span v-if="errors.company_ids" class="error-message">{{ errors.company_ids[0] }}</span>
      </div>
      
      <div class="form-actions">
        <button type="button" @click="goBack" class="btn-cancel">Cancel</button>
        <button type="submit" :disabled="submitting" class="btn-submit">
          {{ submitting ? 'Saving...' : (isEdit ? 'Update Fund' : 'Create Fund') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { fundApi, fundManagerApi, companyApi } from '../api.js';

export default {
  name: 'FundForm',
  props: {
    id: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      form: {
        name: '',
        start_year: new Date().getFullYear(),
        fund_manager_id: '',
        aliases: [],
        company_ids: []
      },
      fundManagers: [],
      companies: [],
      loading: false,
      submitting: false,
      formError: null,
      errors: {}
    };
  },
  computed: {
    isEdit() {
      return !!this.id;
    }
  },
  mounted() {
    this.fetchFundManagers();
    this.fetchCompanies();
    
    if (this.isEdit) {
      this.fetchFund();
    }
  },
  methods: {
    async fetchFundManagers() {
      try {
        const response = await fundManagerApi.list();
        this.fundManagers = response.data;
      } catch (err) {
        console.error('Error fetching fund managers:', err);
      }
    },
    
    async fetchCompanies() {
      try {
        const response = await companyApi.list();
        this.companies = response.data;
      } catch (err) {
        console.error('Error fetching companies:', err);
      }
    },
    
    async fetchFund() {
      this.loading = true;
      
      try {
        const response = await fundApi.get(this.id);
        const fund = response.data;
        
        this.form = {
          name: fund.name,
          start_year: fund.start_year,
          fund_manager_id: fund.fund_manager_id,
          aliases: fund.aliases ? fund.aliases.map(a => a.name) : [],
          company_ids: fund.companies ? fund.companies.map(c => c.id) : []
        };
      } catch (err) {
        this.formError = err.response?.data?.message || 'Failed to load fund';
        console.error('Error fetching fund:', err);
      } finally {
        this.loading = false;
      }
    },
    
    addAlias() {
      this.form.aliases.push('');
    },
    
    removeAlias(index) {
      this.form.aliases.splice(index, 1);
    },
    
    async submitForm() {
      this.submitting = true;
      this.formError = null;
      this.errors = {};
      
      try {
        // Filter out empty aliases
        const payload = {
          ...this.form,
          aliases: this.form.aliases.filter(alias => alias.trim() !== '')
        };
        
        if (this.isEdit) {
          await fundApi.update(this.id, payload);
        } else {
          await fundApi.create(payload);
        }
        
        // Navigate back to fund list
        this.$router.push({ name: 'fund-list' });
      } catch (err) {
        if (err.response?.status === 422) {
          // Validation errors
          this.errors = err.response.data.errors || {};
          this.formError = err.response.data.message || 'Validation failed';
        } else {
          this.formError = err.response?.data?.message || 'Failed to save fund';
        }
        console.error('Error submitting form:', err);
      } finally {
        this.submitting = false;
      }
    },
    
    goBack() {
      this.$router.push({ name: 'fund-list' });
    }
  }
}
</script>

<style scoped>
.fund-form {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  max-width: 600px;
  margin: 0 auto;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #666;
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

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
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

.help-text {
  font-size: 0.875rem;
  color: #666;
  margin-top: 0.25rem;
}

.alias-input {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.alias-input input {
  flex: 1;
}

.btn-add,
.btn-remove,
.btn-cancel,
.btn-submit {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-add {
  background-color: #4caf50;
  color: white;
}

.btn-add:hover {
  background-color: #45a049;
}

.btn-remove {
  background-color: #d32f2f;
  color: white;
}

.btn-remove:hover {
  background-color: #c62828;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 2rem;
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
