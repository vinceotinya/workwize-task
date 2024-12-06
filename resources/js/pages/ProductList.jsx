import { useAuth } from '@/context/AuthContext.jsx';
import axiosClient from '@/utils/axios.js';
import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

const formDefaultValue = {
  name: '',
  description: '',
  price: '',
  sku: '',
  stock: '',
  status: 'active'
}
const ProductList = () => {
  const { user } = useAuth();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [products, setProducts] = useState([]);
  const navigate = useNavigate()
  const [formData, setFormData] = useState({...formDefaultValue});

  const getProducts = useCallback((async () => {
    try {
      const res = await axiosClient.get('/products');
      setProducts(res.data.data.items ?? []);
    } catch (e) {
      alert(e.message)
      setProducts([])
    }
  }), [])

  const handleSubmit = async () => {
    if (user?.role !== 'supplier') return
    setProducts([{ ...formData, price: parseFloat(formData.price), stock: parseInt(formData.stock) }, ...products ]);
    try {
      const res = await axiosClient.post('/products', {
        ...formData,
        price: parseFloat(formData.price),
        stock: parseInt(formData.stock),
        supplier: user.id
      });
      if (res.data) {
        setProducts([...products, res.data.data]);
        getProducts()
      }
      setIsModalOpen(false);
      setFormData({
        ...formDefaultValue
      });
    } catch (e) {
      alert(e.message)
    }
  };

  useEffect(() => {
    setFormData({...formDefaultValue});
  }, [isModalOpen]);

  useEffect(() => {
    getProducts()
  }, []);

  return (
    <div className="container mx-auto p-6">
      <div className="flex items-center mb-6">
        <h1 className="text-2xl font-bold">Products</h1>
        <div className="flex ml-auto items-center gap-4">
          <button
            onClick={() => navigate('/')}
            className="flex items-center gap-2 border border-blue-600 text-blue-600 px-4 py-2 rounded-lg"
          >
            Back to dashboard
          </button>
          {user?.role === 'supplier' && (
            <button
              onClick={() => setIsModalOpen(true)}
              className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
            >
              + Add Product
            </button>
          )}
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {products.map((product, index) => (
          <div key={index} className="bg-white p-4 rounded-lg shadow">
            <h3 className="font-semibold text-lg">{product.name}</h3>
            <p className="text-gray-600 text-sm mt-1">{product.description}</p>
            <div className="mt-4 space-y-2">
              <p className="text-blue-600 font-semibold">${product.price}</p>
              <p className="text-sm">SKU: {product.sku}</p>
              <p className="text-sm">Stock: {product.stock}</p>
              <span className={`inline-block px-2 py-1 text-xs rounded-full ${
                product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
              }`}>
                {product.status}
              </span>
            </div>
          </div>
        ))}
      </div>

      {isModalOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-lg p-6 max-w-md w-full">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-semibold">Add New Product</h2>
              <button
                onClick={() => setIsModalOpen(false)}
                className="text-gray-500 hover:text-gray-700"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Name</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({...formData, name: e.target.value})}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  value={formData.description}
                  onChange={(e) => setFormData({...formData, description: e.target.value})}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  rows="3"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Price</label>
                <input
                  type="number"
                  step="0.01"
                  value={formData.price}
                  onChange={(e) => setFormData({...formData, price: e.target.value})}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">SKU</label>
                <input
                  type="text"
                  value={formData.sku}
                  onChange={(e) => setFormData({...formData, sku: e.target.value})}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Stock</label>
                <input
                  type="number"
                  value={formData.stock}
                  onChange={(e) => setFormData({...formData, stock: e.target.value})}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Status</label>
                <select
                  value={formData.status}
                  onChange={(e) => setFormData({...formData, status: e.target.value})}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div className="flex justify-end gap-2 mt-6">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                  onClick={handleSubmit}
                >
                  Create Product
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default ProductList;