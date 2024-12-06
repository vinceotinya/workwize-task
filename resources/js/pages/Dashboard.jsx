import { useNavigate } from 'react-router-dom';

const Dashboard = () => {
  const navigate = useNavigate();
  return (
    <div className="max-w-7xl mx-auto px-4 py-6">
      <h2 className="text-2xl font-bold mb-4">Welcome to your Dashboard</h2>
      <button
        onClick={() => {
          navigate('/products')
        }}
        className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
      >
        Go to products page
      </button>
      <button
        onClick={() => {
          navigate('/orders')
        }}
        className="ml-4 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
      >
        Go to orders page
      </button>
    </div>
  );
};

export default Dashboard;