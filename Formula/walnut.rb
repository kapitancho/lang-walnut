# typed: false
# frozen_string_literal: true

class Walnut < Formula
  desc "Strongly-typed, interpreted programming language with compile-time type checking"
  homepage "https://github.com/walnut-lang/walnut"
  license "MIT"
  version "0.1.4"

  url "https://github.com/walnut-lang/walnut/releases/download/v#{version}/walnut.phar"
  sha256 "474086a4e84d5a6d8d428c3fa50fb5c2e8524550aa7a8e94ea8fab24287d20f5"

  depends_on "php" => :runtime

  def install
    bin.install "walnut.phar" => "walnut"
  end

  test do
    assert_match(/Walnut \d+\.\d+\.\d+/, shell_output("#{bin}/walnut --version"))
  end
end