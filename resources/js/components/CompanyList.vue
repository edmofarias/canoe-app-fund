<template>
  <div class="company-list">
    <div class="list-header">
      <h2>Companies</h2>
      <button @click="createNew" class="btn-create">Create New</button>
    </div>
    
    <div v-if="loading" class="loading">Loading companies...</div>
    
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <div v-else>
      <div v-if="companies.length === 0" class="empty-state">
        No companies found.
      </div>
      
      <div v-else class="items-container">
        <div v-for="company in companies" :key="company.id" class="item-card">
          <div class="item-header">
            <h3>{{ company.name }}</h3>
            <div class="item-actions">
              <button @click="confirmDelete(company)" class="btn-delete">Delete</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="cancelDelete">
      <div class="modal" @click.stop>
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete "{{ itemToDelete?.name }}"?</p>
        <div class="modal-actions">
          <button @click="cancelDelete" class="btn-cancel">Cancel</button>
          <button @click="deleteItem" class="btn-confirm-delete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { companyApi } from '../api.js';

export default {
  name: 'CompanyList',
  data() {
    return {
      companies: [],
      loading: false,
      error: null,
      showDeleteModal: false,
      itemToDelete: null
    };
  },
  mounted() {
    this.fetchCompanies();
  },
  methods: {
    async fetchCompanies() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await companyApi.list();
        this.companies = response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load companies';
        console.error('Error fetching companies:', err);
      } finally {
        this.loading = false;
      }
    },
    
    createNew() {
      this.$router.push({ name: 'company-create' });
    },
    
    confirmDelete(company) {
      this.itemToDelete = company;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
      this.itemToDelete = null;
    },
    
    async deleteItem() {
      if (!this.itemToDelete) return;
      
      try {
        await companyApi.delete(this.itemToDelete.id);
        this.showDeleteModal = false;
        this.itemToDelete = null;
        await this.fetchCompanies();
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete company';
        console.error('Error deleting company:', err);
        this.showDeleteModal = false;
      }
    }
  }
}
</script>

<style scoped>
.company-list {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.list-header h2 {
  margin: 0;
  color: #333;
}

.btn-create {
  padding: 0.5rem 1rem;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-create:hover {
  background-color: #45a049;
}

.loading, .error, .empty-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error {
  color: #d32f2f;
}

.items-container {
  margin-top: 1rem;
}

.item-card {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 1rem 1.5rem;
  margin-bottom: 0.75rem;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-header h3 {
  margin: 0;
  color: #333;
  font-size: 1.1rem;
}

.item-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-delete, .btn-cancel, .btn-confirm-delete {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-delete {
  background-color: #d32f2f;
  color: white;
}

.btn-delete:hover {
  background-color: #c62828;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 400px;
  width: 90%;
}

.modal h3 {
  margin-top: 0;
  color: #333;
}

.modal p {
  color: #666;
  margin: 1rem 0;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.btn-cancel {
  background-color: #757575;
  color: white;
}

.btn-cancel:hover {
  background-color: #616161;
}

.btn-confirm-delete {
  background-color: #d32f2f;
  color: white;
}

.btn-confirm-delete:hover {
  background-color: #c62828;
}
</style>
