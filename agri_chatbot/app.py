from flask import Flask, request, jsonify, send_from_directory
from ai_engine import answer_with_ai
from ml_engine import predict_with_ml

app = Flask(__name__, static_folder="static")

@app.route("/")
def index():
    return send_from_directory("static", "chatbot.html")

@app.route("/api/ask", methods=["POST"])
def api_ask():
    data = request.json or {}
    question = data.get("question", "").strip()

    if not question:
        return jsonify({"error": "No question provided"}), 400

    answer = answer_with_ai(question)
    return jsonify({"answer": answer})

@app.route("/api/predict", methods=["POST"])
def api_predict():
    data = request.json or {}
    task = data.get("task")
    features = data.get("features", {})

    if not task:
        return jsonify({"error": "No task provided"}), 400

    result = predict_with_ml(task, features)
    return jsonify({"result": result})

if __name__ == "__main__":
    app.run(debug=True)
