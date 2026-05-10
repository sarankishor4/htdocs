from flask import Flask, request, jsonify
from credit_scorer import calculate_score
from skill_assessor import assess_skills

app = Flask(__name__)

@app.route('/api/score', methods=['POST'])
def get_score():
    data = request.json
    skills = data.get('skills', [])
    history = data.get('history', {})
    score = calculate_score(skills, history)
    return jsonify({'success': True, 'score': score})

@app.route('/api/assess', methods=['POST'])
def assess():
    text = request.json.get('text', '')
    skills = assess_skills(text)
    return jsonify({'success': True, 'skills': skills})

if __name__ == '__main__':
    app.run(port=5001, debug=True)
