import os
import logging
from flask import Flask, request, jsonify
from flask_cors import CORS
from anomaly_detector import AnomalyDetector

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app, resources={r"/api/*": {"origins": "*"}})

detector = AnomalyDetector()


@app.route('/api/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'healthy',
        'service': 'monitor-ia',
        'models_loaded': detector.is_loaded(),
        'version': '1.0.0'
    })


@app.route('/api/anomaly/detect', methods=['POST'])
def detect_anomaly():
    try:
        data = request.get_json()
        if not data:
            return jsonify({'error': 'No data provided'}), 400

        server_id = data.get('server_id')
        server_name = data.get('server_name', 'unknown')
        metric_type = data.get('metric_type', 'cpu')
        metrics = data.get('data', [])

        if not metrics:
            return jsonify({
                'server_id': server_id,
                'server_name': server_name,
                'metric_type': metric_type,
                'anomaly': False,
                'score': 0.0,
                'severity': 'low',
                'description': 'Pas assez de données pour analyser',
                'recommendation': 'Collecter plus de métriques',
            })

        result = detector.detect(metrics, metric_type, server_name)
        result['server_id'] = server_id
        result['server_name'] = server_name
        result['metric_type'] = metric_type

        logger.info(f"Anomaly detection for {server_name}/{metric_type}: score={result['score']}, anomaly={result['anomaly']}")

        return jsonify(result)

    except Exception as e:
        logger.error(f"Error in detect_anomaly: {str(e)}")
        return jsonify({'error': str(e)}), 500


@app.route('/api/anomaly/detect-all', methods=['POST'])
def detect_all():
    try:
        results = detector.detect_all_patterns()
        return jsonify({
            'anomalies': results,
            'total_detected': len(results)
        })
    except Exception as e:
        logger.error(f"Error in detect_all: {str(e)}")
        return jsonify({'error': str(e)}), 500


@app.route('/api/anomaly/stats', methods=['GET'])
def anomaly_stats():
    try:
        stats = detector.get_stats()
        return jsonify(stats)
    except Exception as e:
        logger.error(f"Error in anomaly_stats: {str(e)}")
        return jsonify({'error': str(e)}), 500


@app.route('/api/anomaly/train', methods=['POST'])
def train_model():
    try:
        data = request.get_json()
        metric_type = data.get('metric_type', 'cpu')
        training_data = data.get('data', [])

        if len(training_data) < 10:
            return jsonify({'error': 'Need at least 10 data points for training'}), 400

        result = detector.train(metric_type, training_data)
        return jsonify(result)

    except Exception as e:
        logger.error(f"Error in train_model: {str(e)}")
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port, debug=True)