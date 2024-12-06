import axiosClient from '@/utils/axios.js';
import React, { useCallback, useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';

const Orders = () => {
  const [products, setProducts] = useState([]);
  const { user } = useAuth();
  const navigate = useNavigate()
  const getProducts = useCallback((async () => {
    try {
      const res = await axiosClient.get('/products');
      setProducts(res.data.data.items ?? []);
    } catch (e) {
      alert(e.message)
      setProducts([])
    }
  }), [])
  const [orders, setOrders] = useState([]);
  const getOrders = useCallback((async () => {
    try {
      const res = await axiosClient.get('/orders');
      setOrders(res.data.data.items ?? []);
    } catch (e) {
      alert(e.message)
      setOrders([])
    }
  }), [])

  useEffect(() => {
    getProducts()
    getOrders()
  }, []);

  const [isOrderModalOpen, setIsOrderModalOpen] = useState(false);
  const [isDeliveryModalOpen, setIsDeliveryModalOpen] = useState(false);
  const [selectedOrder, setSelectedOrder] = useState(null);

  const [newOrder, setNewOrder] = useState({
    items: []
  });
  const [deliveryForm, setDeliveryForm] = useState([])

  useEffect(() => {
    setNewOrder({ items: [] });
  }, [isOrderModalOpen]);
  useEffect(() => {
    setDeliveryForm([]);
  }, [isDeliveryModalOpen]);

  const updateDeliveryStatus = (orderId, itemIndex, updates) => {
    const order = orders.find(order => order.id === orderId);
    const originalItem = order?.items[itemIndex];
    if (originalItem) {
      // updates sample: { delivery_time: '2021-09-01', status: 'delivered' }
      setDeliveryForm(prev => {
        if (prev.find(item => item.id === originalItem.id)) {
          return prev.map((item) => {
            if (originalItem.id === item.id) {
              return { ...originalItem, ...updates };
            }
            return item;
          })
        } else {
          return [...prev, { id: originalItem.id, ...updates }]
        }
      })
    }
  }
  const syncDeliveryForm = async () => {
    try {
      for (const item of deliveryForm) {
        await axiosClient.put(`/orders/${item.id}/delivery`, {
          status: item.status,
          delivery_time: item.delivery_time
        })
      }

      setIsDeliveryModalOpen(false);
      setSelectedOrder(null);
      getOrders()
    } catch (e) {
      alert(e.message)
    }
  }

  const addItemToOrder = () => {
    setNewOrder({
      ...newOrder,
      items: [...newOrder.items, { product_id: '', quantity: 1 }]
    });
  };

  const handleItemChange = (index, field, value) => {
    const updatedItems = newOrder.items.map((item, i) => {
      if (i === index) {
        return { ...item, [field]: value };
      }
      return item;
    });
    setNewOrder({ ...newOrder, items: updatedItems });
  };

  const handleCreateOrder = async (e) => {
    try {
      await axiosClient.post('/orders', newOrder)
      await getOrders()
      setIsOrderModalOpen(false)
    } catch (e) {
      alert(e.message)
    }
  };
  return (
    <div className="container mx-auto p-6">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">Orders</h1>
        <button
          onClick={() => navigate('/')}
          className="flex items-center gap-2 border border-blue-600 text-blue-600 px-4 py-2 rounded-lg"
        >
          Back to dashboard
        </button>
        {user?.role === 'admin' && (
          <button
            onClick={() => setIsOrderModalOpen(true)}
            className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
          >
            + Create Order
          </button>
        )}
      </div>

      <div className="space-y-6">
        {orders.map((order) => (
          <div key={order.id} className="bg-white p-6 rounded-lg shadow">
            <h2 className="text-lg font-semibold mb-4">Order #{order.id}</h2>
            <div className="space-y-4">
              {order.items.map((item, index) => (
                <div key={index} className="flex items-center justify-between border-b pb-4">
                  <div>
                    <p className="font-medium">{item.product_name}</p>
                    <p className="text-sm text-gray-600">Quantity: {item.quantity}</p>
                    <p className="text-sm text-gray-600">
                      Delivery: {item.delivery_time || 'Not scheduled'}
                    </p>
                    <span className={`inline-block px-2 py-1 text-xs rounded-full ${
                      item.delivery_status === 'delivered' ? 'bg-green-100 text-green-800' :
                        item.delivery_status === 'in_transit' ? 'bg-blue-100 text-blue-800' :
                          'bg-yellow-100 text-yellow-800'
                    }`}>
                      {item.delivery_status}
                    </span>
                  </div>
                  {user?.role === 'supplier' && (
                    <button
                      onClick={() => {
                        setSelectedOrder({
                          orderId: order.id,
                          itemIndex: index,
                          item: JSON.parse(JSON.stringify(item))
                        });
                        setIsDeliveryModalOpen(true);
                      }}
                      className="flex items-center gap-2 border border-blue-600 text-blue-600 hover:text-blue-800"
                    >

                      Update Delivery
                    </button>)}
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>

      {/* Create Order Modal */}
      {isOrderModalOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-lg p-6 max-w-md w-full">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-semibold">Create New Order</h2>
              <button
                onClick={() => setIsOrderModalOpen(false)}
                className="text-gray-500 hover:text-gray-700"
              >
                ×
              </button>
            </div>

            <form className="space-y-4">
              {newOrder.items.map((item, index) => (
                <div key={index} className="space-y-4 p-4 border rounded-lg">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Product</label>
                    <select
                      value={item.product_id}
                      onChange={(e) => handleItemChange(index, 'product_id', e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                      <option value="">Select Product</option>
                      {products.map(product => (
                        <option key={product.id} value={product.id}>
                          {product.name}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Quantity</label>
                    <input
                      type="number"
                      min="1"
                      value={item.quantity}
                      onChange={(e) => handleItemChange(index, 'quantity', parseInt(e.target.value))}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    />
                  </div>
                </div>
              ))}

              <button
                type="button"
                onClick={addItemToOrder}
                className="w-full py-2 px-4 border border-blue-500 text-blue-500 rounded-md hover:bg-blue-50"
              >
                Add Another Item
              </button>

              <div className="flex justify-end gap-2 mt-6">
                <button
                  type="button"
                  onClick={() => setIsOrderModalOpen(false)}
                  className="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                  onClick={handleCreateOrder}
                >
                  Create Order
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Update Delivery Modal */}
      {isDeliveryModalOpen && selectedOrder && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-lg p-6 max-w-md w-full">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-semibold">Update Delivery Status</h2>
              <button
                onClick={() => {
                  setIsDeliveryModalOpen(false);
                  setSelectedOrder(null);
                }}
                className="text-gray-500 hover:text-gray-700"
              >
                ×
              </button>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Delivery Time</label>
                <input
                  type="date"
                  value={selectedOrder.item.delivery_time || ''}
                  onChange={(e) => {
                    updateDeliveryStatus(selectedOrder.orderId, selectedOrder.itemIndex, {
                      delivery_time: e.target.value
                    });
                  }}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Status</label>
                <select
                  value={selectedOrder.item.status}
                  onChange={(e) => {
                    updateDeliveryStatus(selectedOrder.orderId, selectedOrder.itemIndex, {
                      delivery_status: e.target.value
                    });
                  }}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="pending">Pending</option>
                  <option value="in_transit">In Transit</option>
                  <option value="delivered">Delivered</option>
                </select>
              </div>

              <div className="flex justify-end gap-2 mt-6">
                <button
                  onClick={async () => {
                    await syncDeliveryForm()
                  }}
                  className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                  Done
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Orders;