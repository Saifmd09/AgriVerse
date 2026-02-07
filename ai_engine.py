import os
from groq import Groq

GROQ_API_KEY = os.environ.get("GROQ_API_KEY")
if not GROQ_API_KEY:
    raise RuntimeError("GROQ_API_KEY not set")

client = Groq(api_key=GROQ_API_KEY)

SYSTEM_PROMPT = """
You are AgriSage, an expert agriculture assistant.
Give clear, practical, and actionable advice to farmers.
Keep responses concise and useful.
"""

def answer_with_ai(question: str) -> str:
    response = client.chat.completions.create(
        model="llama-3.3-70b-versatile",
        messages=[
            {"role": "system", "content": SYSTEM_PROMPT},
            {"role": "user", "content": question}
        ],
        temperature=0.4,
        max_tokens=500
    )

    return response.choices[0].message.content.strip()
